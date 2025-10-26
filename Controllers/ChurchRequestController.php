<?php
// Controller for handling church requests from organizers
// Organizers (role_id = 2) can submit a request for a new church
// Admins (role_id = 1) can review and approve or reject requests
// Requests are stored in the church_requests table, not churches

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../Models/Church.php';

class ChurchRequestController
{
    // allow only organizer or admin (2 or 1)
    private static function requireOrganizerOrAdmin()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;
        if (!in_array($role, [1, 2], true)) {
            http_response_code(403);
            die('Access denied');
        }
    }

    // allow only admin (1)
    private static function requireAdmin()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;
        if ($role !== 1) {
            http_response_code(403);
            die('Access denied');
        }
    }

    // show form organizers use to request a church be added
    // route example: GET /churchevents/church/request
    public static function requestForm()
    {
        self::requireOrganizerOrAdmin();

        self::view('church/request_form');
    }

    // handle POST of a new church request from an organizer
    // route example: POST /churchevents/church/request/store
    public static function storeRequest()
    {
        self::requireOrganizerOrAdmin();

        if (!verify_csrf()) {
            return self::view('church/request_form', ['error' => 'Invalid form token.']);
        }

        start_session_once();
        $requester_name  = $_SESSION['user']['name']  ?? 'Unknown';
        $requester_email = $_SESSION['user']['email'] ?? 'unknown@example.com';

        // sanitize and normalize request data
        $church_name = trim($_POST['church_name'] ?? '');
        $address1    = trim($_POST['address1'] ?? '');
        $address2    = trim($_POST['address2'] ?? '');
        $city        = trim($_POST['city'] ?? '');
        $state       = strtoupper(trim($_POST['state'] ?? ''));
        $postal_code = trim($_POST['postal_code'] ?? '');

        // basic validation checks
        if (
            strlen($church_name) < 2 ||
            strlen($city) < 1 ||
            strlen($state) !== 2 ||
            strlen($postal_code) < 3
        ) {
            return self::view('church/request_form', [
                'error' => 'Missing or invalid fields.',
                'old' => [
                    'church_name' => $church_name,
                    'address1' => $address1,
                    'address2' => $address2,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $postal_code
                ]
            ]);
        }

        // check if this church already exists in approved list
        $dupChurchId = Church::exists(
            $church_name,
            $city,
            $state,
            $postal_code
        );

        // set initial status for the request
        // if a match exists in churches, flag this as duplicate
        // else mark as pending for admin review
        $status = $dupChurchId ? 'duplicate' : 'pending';

        // insert into church_requests table
        $stmt = db()->prepare("
            INSERT INTO church_requests
            (requester_name, requester_email, church_name, address1, address2, city, state, postal_code, status, church_id, duplicate_of_church_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        // if duplicate, set duplicate_of_church_id to that match and church_id NULL
        // if pending, set both to NULL so admin can resolve
        $church_id_val = null;
        $dup_id_val = $dupChurchId ?: null;

        $stmt->bind_param(
            'ssssssssssii',
            $requester_name,
            $requester_email,
            $church_name,
            $address1,
            $address2,
            $city,
            $state,
            $postal_code,
            $status,
            $church_id_val,
            $dup_id_val
        );
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            return self::view('church/request_form', [
                'error' => 'Could not submit request.'
            ]);
        }

        // show message back to organizer
        $msg = $status === 'duplicate'
            ? 'This church already exists. Admin will review.'
            : 'Your request was submitted and is pending approval.';

        return self::view('church/request_form', [
            'success' => $msg
        ]);
    }

    // list pending and duplicate requests for admin review
    // route example: GET /churchevents/church/requests
    public static function reviewList()
    {
        self::requireAdmin();

        $res = db()->query("
            SELECT id,
                   requester_name,
                   requester_email,
                   church_name,
                   address1,
                   address2,
                   city,
                   state,
                   postal_code,
                   status,
                   created_at
            FROM church_requests
            WHERE status IN ('pending','duplicate')
            ORDER BY created_at ASC
        ");
        $requests = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        self::view('church/requests_admin', compact('requests'));
    }

    // admin approval endpoint
    // creates a real row in churches, links it to the request, and marks request approved
    // route example: POST /churchevents/church/requests/approve
    public static function approve()
    {
        self::requireAdmin();

        if (!verify_csrf()) {
            http_response_code(400);
            die('Invalid form token.');
        }

        $reqId = (int)($_POST['request_id'] ?? 0);
        if ($reqId <= 0) {
            http_response_code(400);
            die('Missing request id.');
        }

        // fetch the request row
        $stmt = db()->prepare("SELECT * FROM church_requests WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $reqId);
        $stmt->execute();
        $res = $stmt->get_result();
        $reqRow = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$reqRow) {
            http_response_code(404);
            die('Request not found.');
        }

        // if this was already linked/approved could bail here
        // but basic version always proceeds

        // safety: check if an identical church now exists already
        $existsNow = Church::exists(
            $reqRow['church_name'],
            $reqRow['city'],
            $reqRow['state'],
            $reqRow['postal_code']
        );

        $db = db();
        $db->begin_transaction();
        try {
            $newChurchId = null;

            // only insert into churches if not already there
            if (!$existsNow) {
                $newChurch = new Church();
                $newChurch->fill([
                    'name' => $reqRow['church_name'],
                    'address1' => $reqRow['address1'],
                    'address2' => $reqRow['address2'],
                    'city' => $reqRow['city'],
                    'state' => $reqRow['state'],
                    'postal_code' => $reqRow['postal_code'],
                    'latitude' => null,
                    'longitude' => null,
                    'is_active' => 1
                ]);
                $ok = $newChurch->save();
                if (!$ok || $newChurch->id === null) {
                    throw new Exception('Failed to create church');
                }
                $newChurchId = $newChurch->id;
            } else {
                // if a matching church already exists, re-use that id
                // select it now
                $stmt2 = $db->prepare("
                    SELECT id FROM churches
                    WHERE name = ? AND city = ? AND state = ? AND postal_code = ?
                    LIMIT 1
                ");
                $stmt2->bind_param(
                    'ssss',
                    $reqRow['church_name'],
                    $reqRow['city'],
                    $reqRow['state'],
                    $reqRow['postal_code']
                );
                $stmt2->execute();
                $r2 = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();
                $newChurchId = $r2 ? (int)$r2['id'] : null;
            }

            // update the request row to approved
            $stmt3 = $db->prepare("
                UPDATE church_requests
                SET status='approved',
                    church_id=?,
                    reviewed_at=NOW()
                WHERE id=?
            ");
            $stmt3->bind_param('ii', $newChurchId, $reqId);
            $stmt3->execute();
            $stmt3->close();

            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            http_response_code(500);
            die('Could not approve request.');
        }

        // After approving, send admin back to review list
        header('Location: /churchevents/church/requests');
        exit;
    }

    // admin rejection endpoint
    // sets the request status to rejected
    // route example: POST /churchevents/church/requests/reject
    public static function reject()
    {
        self::requireAdmin();

        if (!verify_csrf()) {
            http_response_code(400);
            die('Invalid form token.');
        }

        $reqId = (int)($_POST['request_id'] ?? 0);
        if ($reqId <= 0) {
            http_response_code(400);
            die('Missing request id.');
        }

        $stmt = db()->prepare("
            UPDATE church_requests
            SET status='rejected', reviewed_at=NOW()
            WHERE id=?
        ");
        $stmt->bind_param('i', $reqId);
        $stmt->execute();
        $stmt->close();

        header('Location: /churchevents/church/requests');
        exit;
    }

    // shared view loader (same pattern as ChurchController)
    private static function view(string $name, array $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . "/../Views/{$name}.php";
        require __DIR__ . "/../Views/layout.php";
    }
}
