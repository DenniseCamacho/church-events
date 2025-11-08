<?php
// FILE: Controllers/ChurchRequestController.php
// PURPOSE: Handle church requests from organizers; admins review and approve/reject

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../Models/Church.php';

class ChurchRequestController
{
    // ALLOW ORGANIZER OR ADMIN
    private static function requireOrganizerOrAdmin()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;
        if (!in_array($role, [1, 2], true)) {
            http_response_code(403);
            die('Access denied');
        }
    }

    // ALLOW ADMIN ONLY
    private static function requireAdmin()
    {
        start_session_once();
        $role = $_SESSION['user']['role_id'] ?? 0;
        if ($role !== 1) {
            http_response_code(403);
            die('Access denied');
        }
    }

    // SHOW REQUEST FORM
    public static function requestForm(array $data = [])
    {
        self::requireOrganizerOrAdmin();
        self::view('church/request_form', $data);
    }

    // STORE REQUEST
    public static function storeRequest()
    {
        self::requireOrganizerOrAdmin();

        if (!verify_csrf()) {
            return self::requestForm(['error' => 'Invalid form token.']);
        }

        start_session_once();

        // requester comes from session, not form
        $requester_name  = $_SESSION['user']['name']  ?? 'Unknown';
        $requester_email = $_SESSION['user']['email'] ?? 'unknown@example.com';

        // form fields
        $church_name = trim($_POST['church_name'] ?? '');
        $address1    = trim($_POST['address1'] ?? '');
        $address2    = trim($_POST['address2'] ?? '');
        $city        = trim($_POST['city'] ?? '');
        $state       = strtoupper(trim($_POST['state'] ?? ''));
        $postal_code = trim($_POST['postal_code'] ?? '');

        // VALIDATION
        $errors = [];

        if ($church_name === '' || mb_strlen($church_name) > 160) {
            $errors[] = 'Church name is required and must be 160 characters or less.';
        }

        if ($city === '' || mb_strlen($city) > 120) {
            $errors[] = 'City is required.';
        }

        if ($state === '' || strlen($state) !== 2) {
            $errors[] = 'State must be 2 letters.';
        }

        // postal is optional, only check length if filled
        if ($postal_code !== '' && strlen($postal_code) > 10) {
            $errors[] = 'Postal code must be 10 characters or less.';
        }

        // prevent putting email into address field
        if ($address1 !== '' && strpos($address1, '@') !== false) {
            $errors[] = 'Address 1 cannot contain an email address.';
        }

        if (!empty($errors)) {
            return self::requestForm([
                'errors' => $errors,
                'old'    => [
                    'church_name' => $church_name,
                    'address1'    => $address1,
                    'address2'    => $address2,
                    'city'        => $city,
                    'state'       => $state,
                    'postal_code' => $postal_code
                ]
            ]);
        }

        // CHECK IF CHURCH ALREADY EXISTS
        // Church::exists(...) returns bool in your model
        $alreadyExists = Church::exists(
            $church_name,
            $city,
            $state,
            $postal_code
        );

        // STATUS
        $status = $alreadyExists ? 'duplicate' : 'pending';

        // PREPARE INSERT
        // created_at is handled by the table default, so NOT added here
        $sql = "
            INSERT INTO church_requests
                (requester_name, requester_email, church_name, address1, address2, city, state, postal_code, status, church_id, duplicate_of_church_id)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = db()->prepare($sql);

        // 9 strings + 2 ints = sssssssssii
        $church_id_val = null;
        $dup_id_val    = null;

        $stmt->bind_param(
            'sssssssssii',
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
            return self::requestForm([
                'error' => 'Could not submit request right now.',
                'old'   => [
                    'church_name' => $church_name,
                    'address1'    => $address1,
                    'address2'    => $address2,
                    'city'        => $city,
                    'state'       => $state,
                    'postal_code' => $postal_code
                ]
            ]);
        }

        $msg = $status === 'duplicate'
            ? 'This church looks like it already exists. Admin will review.'
            : 'Your request was submitted and is pending approval.';

        return self::requestForm(['success' => $msg]);
    }

    // ADMIN REVIEW LIST
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

        self::view('church/requests_admin', ['requests' => $requests]);
    }

    // ADMIN APPROVE
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

        $db = db();
        $db->begin_transaction();

        try {
            // check if church now exists
            $existsNow = Church::exists(
                $reqRow['church_name'],
                $reqRow['city'],
                $reqRow['state'],
                $reqRow['postal_code']
            );

            $newChurchId = null;

            if (!$existsNow) {
                $newChurch = new Church();
                $newChurch->fill([
                    'name'        => $reqRow['church_name'],
                    'address1'    => $reqRow['address1'],
                    'address2'    => $reqRow['address2'],
                    'city'        => $reqRow['city'],
                    'state'       => $reqRow['state'],
                    'postal_code' => $reqRow['postal_code'],
                    'latitude'    => null,
                    'longitude'   => null,
                    'is_active'   => 1
                ]);
                $ok = $newChurch->save();
                if (!$ok || $newChurch->id === null) {
                    throw new Exception('Could not create church');
                }
                $newChurchId = $newChurch->id;
            } else {
                // pull the id to link the request
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
                $row = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();
                $newChurchId = $row ? (int)$row['id'] : null;
            }

            $stmt3 = $db->prepare("
                UPDATE church_requests
                SET status='approved', church_id=?, reviewed_at=NOW()
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

        header('Location: ' . base_url() . '/church/requests');
        exit;
    }

    // ADMIN REJECT
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

        header('Location: ' . base_url() . '/church/requests');
        exit;
    }

    // VIEW LOADER
    private static function view(string $name, array $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . "/../Views/{$name}.php";
        require __DIR__ . "/../Views/layout.php";
    }
}
