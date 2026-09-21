<?php
include_once(__DIR__ . "/db.php");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include_once("db.php");
/* ================================
   OPTIONS REQUEST
================================ */
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
$method = $_SERVER['REQUEST_METHOD'];
/* ================================
   GET ID
================================ */
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
/* ================================
   GET
   GET ALL
   GET ONE
================================ */
if ($method == 'GET') {
    /* GET ONE */
    if ($id > 0) {
        $stmt = $conn->prepare(
            "SELECT * FROM internships WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            echo json_encode([
                "success" => true,
                "data" => $data
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "Internship not found"
            ]);

        }
        $stmt->close();
    }
    /* GET ALL */
    else {
        $result = mysqli_query(
            $conn,
            "SELECT * FROM internships ORDER BY id DESC"
        );
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }
}
/* ================================
   POST
   ADD INTERNSHIP
================================ */
elseif ($method == 'POST') {
    $input = json_decode(
        file_get_contents("php://input"),
        true
    );
    $errors = validateInternship($input);
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "errors" => $errors
        ]);
        exit();
    }
    $student_name =
        trim($input['student_name']);
    $email =
        trim($input['email']);
    $phone =
        trim($input['phone']);
    $company_name =
        trim($input['company_name']);
    $internship_role =
        trim($input['internship_role']);
    $start_date =
        $input['start_date'];
    $duration =
        intval($input['duration']);
    $stipend =
        intval($input['stipend']);
    $status =
        trim($input['status']);
    $stmt = $conn->prepare(
        "INSERT INTO internships
        (
            student_name,
            email,
            phone,
            company_name,
            internship_role,
            start_date,
            duration,
            stipend,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )"
    );
    $stmt->bind_param(
        "ssssssiis",
        $student_name,
        $email,
        $phone,
        $company_name,
        $internship_role,
        $start_date,
        $duration,
        $stipend,
        $status
    );
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" =>
                "Internship added successfully",
            "id" =>
                $conn->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" =>
                "Failed to add internship",
            "error" =>
                $stmt->error
        ]);
    }
    $stmt->close();
}
/* ================================
   PUT
   UPDATE INTERNSHIP
================================ */
elseif ($method == 'PUT') {
    /* CHECK ID */
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" =>
                "Internship ID is required"
        ]);
        exit();
    }
    /* CHECK INTERNSHIP EXISTS */
    $check = $conn->prepare(
        "SELECT id
         FROM internships
         WHERE id = ?"
    );
    $check->bind_param(
        "i",
        $id
    );
    $check->execute();
    $result =
        $check->get_result();
    if ($result->num_rows == 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" =>
                "Internship not found"
        ]);
        exit();
    }
    $check->close();
    /* GET JSON DATA */
    $input = json_decode(
        file_get_contents(
            "php://input"
        ),
        true
    );
    /* VALIDATION */
    $errors =
        validateInternship($input);
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "errors" => $errors
        ]);
        exit();
    }
    $student_name =
        trim($input['student_name']);
    $email =
        trim($input['email']);
    $phone =
        trim($input['phone']);
    $company_name =
        trim($input['company_name']);
    $internship_role =
        trim($input['internship_role']);
    $start_date =
        $input['start_date'];
    $duration =
        intval($input['duration']);
    $stipend =
        intval($input['stipend']);
    $status =
        trim($input['status']);
    /* UPDATE QUERY */
    $stmt = $conn->prepare(
        "UPDATE internships SET
            student_name = ?,
            email = ?,
            phone = ?,
            company_name = ?,
            internship_role = ?,
            start_date = ?,
            duration = ?,
            stipend = ?,
            status = ?
        WHERE id = ?"
    );
    $stmt->bind_param(
        "ssssssiisi",
        $student_name,
        $email,
        $phone,
        $company_name,
        $internship_role,
        $start_date,
        $duration,
        $stipend,
        $status,
        $id
    );
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" =>
                "Internship updated successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" =>
                "Failed to update internship",
            "error" =>
                $stmt->error
        ]);
    }
    $stmt->close();
}
/* ================================
   DELETE
   DELETE INTERNSHIP
================================ */
elseif ($method == 'DELETE') {
    /* CHECK ID */
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" =>
                "Internship ID is required"
        ]);
        exit();
    }
    /* CHECK RECORD */
    $check = $conn->prepare(
        "SELECT id
         FROM internships
         WHERE id = ?"
    );
    $check->bind_param(
        "i",
        $id
    );
    $check->execute();
    $result =
        $check->get_result();
    if ($result->num_rows == 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" =>
                "Internship not found"
        ]);
        exit();
    }
    $check->close();
    /* DELETE */
    $stmt = $conn->prepare(
        "DELETE FROM internships
         WHERE id = ?"
    );
    $stmt->bind_param(
        "i",
        $id
    );
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" =>
                "Internship deleted successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" =>
                "Failed to delete internship",
            "error" =>
                $stmt->error
        ]);
    }
    $stmt->close();
}
/* ================================
   INVALID METHOD
================================ */
else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" =>
            "Method not allowed"
    ]);
}
/* =====================================================
   VALIDATION FUNCTION
===================================================== */
function validateInternship($input)
{
    $errors = [];
    /* STUDENT NAME */
    if (
        !$input ||
        !isset($input['student_name']) ||
        trim($input['student_name']) == ''
    ) {
        $errors['student_name'] =
            "Student name is required";
    }
    /* EMAIL */
    if (
        !isset($input['email']) ||
        trim($input['email']) == ''
    ) {
        $errors['email'] =
            "Email is required";
    }
    elseif (
        !filter_var(
            $input['email'],
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $errors['email'] =
            "Enter a valid email";
    }
    /* PHONE */
    if (
        !isset($input['phone']) ||
        trim($input['phone']) == ''
    ) {
        $errors['phone'] =
            "Phone number is required";
    }
    elseif (
        !preg_match(
            '/^[0-9]{10,15}$/',
            $input['phone']
        )
    ) {
        $errors['phone'] =
            "Phone must contain 10 to 15 digits";
    }
    /* COMPANY */
    if (
        !isset($input['company_name']) ||
        trim($input['company_name']) == ''
    ) {
        $errors['company_name'] =
            "Company name is required";
    }
    /* INTERNSHIP ROLE */
    if (
        !isset($input['internship_role']) ||
        trim($input['internship_role']) == ''
    ) {
        $errors['internship_role'] =
            "Internship role is required";
    }
    /* START DATE */
    if (
        !isset($input['start_date']) ||
        trim($input['start_date']) == ''
    ) {
        $errors['start_date'] =
            "Start date is required";
    }
    else {
        $date =
            DateTime::createFromFormat(
                'Y-m-d',
                $input['start_date']
            );
        if (
            !$date ||
            $date->format('Y-m-d')
            != $input['start_date']
        ) {
            $errors['start_date'] =
                "Start date must be in YYYY-MM-DD format";
        }
    }
    /* DURATION */
    if (
        !isset($input['duration']) ||
        !filter_var(
            $input['duration'],
            FILTER_VALIDATE_INT
        ) ||
        $input['duration'] <= 0
    ) {
        $errors['duration'] =
            "Duration must be an integer greater than 0";
    }
    /* STIPEND */
    if (
        !isset($input['stipend']) ||
        !is_numeric($input['stipend']) ||
        $input['stipend'] < 0
    ) {
        $errors['stipend'] =
            "Stipend must be numeric and cannot be negative";
    }
    /* STATUS */
    if (
        !isset($input['status']) ||
        trim($input['status']) == ''
    ) {
        $errors['status'] =
            "Status is required";
    }
    return $errors;
}
?>