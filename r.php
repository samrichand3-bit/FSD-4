<?php
$jsonFile = 'users.json';
$errors = [];
$successMessage = '';
$name = $email = $password = $confirmPassword = '';
$users = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $errors['name'] = 'Name is required.';
    }
    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }
    if (empty($confirmPassword)) {
        $errors['confirm_password'] = 'Confirm password is required.';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address format.';
    }

    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9!@#$%^&*]/', $password)) {
            $errors['password'] = 'Password must include uppercase, lowercase, and a number or special character.';
        }
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            if ($jsonContent === false) {
                $errors['file_error'] = 'Error reading user data file.';
                $users = [];
            } else {
                $users = json_decode($jsonContent, true);
                if ($users === null && json_last_error() !== JSON_ERROR_NONE) {
                    $users = [];
                }
            }
        } else {
            $users = [];
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $newUser = [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword
            ];

            $users[] = $newUser;

            $newJsonContent = json_encode($users, JSON_PRETTY_PRINT);
            
            if (file_put_contents($jsonFile, $newJsonContent) === false) {
                $errors['file_error'] = 'Error writing user data file. Check file permissions.';
            } else {
                $successMessage = 'Registration successful! You can now log in.';
                $name = $email = '';
            }
        }
    }
}
function get_input_value($fieldName) {
    global $$fieldName;
    return htmlspecialchars($$fieldName ?? '');
}
function display_error($fieldName) {
    global $errors;
    return isset($errors[$fieldName]) ? '<span style="color: red; font-size: 0.9em;">' . htmlspecialchars($errors[$fieldName]) . '</span>' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="r.css">
</head>
<body class="registration-page">
    <div class="page">
        <div class="card">
            <h1>User Registration</h1>
            <p class="intro">Create an account to save your profile. All fields are required.</p>

            <?php 
            if ($successMessage): 
            ?>
                <div class="message success">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php 
            endif; 

            if (isset($errors['file_error'])):
            ?>
                <div class="message error">
                    <?php echo htmlspecialchars($errors['file_error']); ?>
                </div>
            <?php 
            endif; 
            ?>

            <form action="r.php" method="POST">
                
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo get_input_value('name'); ?>" required>
                    <?php echo display_error('name'); ?>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo get_input_value('email'); ?>" required>
                    <?php echo display_error('email'); ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <?php echo display_error('password'); ?>
                    <span class="help-text">Min 8 chars, with uppercase, lowercase, and a number/special character.</span>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <?php echo display_error('confirm_password'); ?>
                </div>

                <div class="form-group">
                    <button type="submit">Register</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>