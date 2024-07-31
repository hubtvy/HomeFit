<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "homefit";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login or handle unauthorized access
    header("Location: index.html"); // Redirect to your login page
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT username, gender, email, year_of_birth, profile_image FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("User not found.");
}

// Fetch survey data
$sql_survey = "SELECT age, weight, height, fitness_goal, health, gym FROM survey WHERE user_id = ?";
$stmt_survey = $conn->prepare($sql_survey);
if ($stmt_survey === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_survey->bind_param("i", $user_id);
$stmt_survey->execute();
$result_survey = $stmt_survey->get_result();

if ($result_survey->num_rows > 0) {
    $survey = $result_survey->fetch_assoc();
} else {
    $survey = [
        'age' => '',
        'weight' => '',
        'height' => '',
        'fitness_goal' => '',
        'health' => '',
        'gym' => ''
    ];
}

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['update_username'] ?? '';
    $new_gender = $_POST['update_gender'] ?? '';
    $new_email = $_POST['update_email'] ?? '';
    $new_year_of_birth = $_POST['update_year_of_birth'] ?? '';
    $new_age = $_POST['update_age'] ?? '';
    $new_weight = $_POST['update_weight'] ?? '';
    $new_height = $_POST['update_height'] ?? '';
    $new_fitness_goal = $_POST['update_fitness_goal'] ?? '';
    $new_health = $_POST['update_health'] ?? '';
    $new_gym = $_POST['update_gym'] ?? '';

    // Process profile image upload
    if (!empty($_FILES['update_profile_image']['name'])) {
        $new_profile_image = $_FILES['update_profile_image']['name'];
        $target_dir = "profile_images/";
        $target_file = $target_dir . basename($new_profile_image);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an actual image
        $check = getimagesize($_FILES['update_profile_image']['tmp_name']);
        if ($check === false) {
            die("File is not an image.");
        }

        // Check file size
        if ($_FILES['update_profile_image']['size'] > 500000) {
            die("Sorry, your file is too large.");
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            die("Sorry, only JPG, JPEG, and PNG files are allowed.");
        }

        // Move uploaded file to target directory
        if (!move_uploaded_file($_FILES['update_profile_image']['tmp_name'], $target_file)) {
            die("Sorry, there was an error uploading your file.");
        }

        // Update profile image in database
        $sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("si", $new_profile_image, $user_id);
        if (!$stmt->execute()) {
            die("Query execution failed: " . $stmt->error);
        }
    }

    // Update other user details in database
    $sql = "UPDATE users SET username = ?, gender = ?, email = ?, year_of_birth = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssssi", $new_username, $new_gender, $new_email, $new_year_of_birth, $user_id);
    if (!$stmt->execute()) {
        die("Query execution failed: " . $stmt->error);
    }

    // Update survey details in database
    $sql_survey_update = "UPDATE survey SET age = ?, weight = ?, height = ?, fitness_goal = ?, health = ?, gym = ? WHERE user_id = ?";
    $stmt_survey_update = $conn->prepare($sql_survey_update);
    if ($stmt_survey_update === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_survey_update->bind_param("ssssssi", $new_age, $new_weight, $new_height, $new_fitness_goal, $new_health, $new_gym, $user_id);
    if (!$stmt_survey_update->execute()) {
        die("Query execution failed: " . $stmt_survey_update->error);
    }

    // Redirect to profile page to show updated info
    header("Location: Profile.html");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="editprofile.css">
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <img src="Images/logo.png" alt="HomeFit">
        <div class="logo_name">HomeFit</div>
    </div>
    <i class='bx bx-menu' id='btn'></i>

    <ul class="nav_list">
        <li><a href="Home.html"><i class='bx bx-home-alt-2'></i><span class="links_name">Home</span></a>
            <span class="tooltip">Home</span>
        </li>
        <li><a href="MyExercise.html"><i class='bx bx-run'></i><span class="links_name">My Exercise</span></a>
            <span class="tooltip">My Exercise</span>
        </li>
        <li><a href="Notes.html"><i class='bx bx-note'></i><span class="links_name">Notes</span></a>
            <span class="tooltip">Notes</span></a>
        </li>
        <li><a href="Profile.html"><i class='bx bx-user'></i><span class="links_name">Profile</span></a>
            <span class="tooltip">Profile</span></a>
        </li>
        <li><a href="History.html"><i class='bx bx-history'></i><span class="links_name">History</span></a>
            <span class="tooltip">History</span></a>
        </li>
        <li><a href="index.html"><i class='bx bx-log-out'></i><span class="links_name">Log Out</span></a>
            <span class="tooltip">Log Out</span></a>
        </li>
    </ul>
</div>
<div class="home_content">
    <div class="header">
         <H1> USER PROFILE</H1>
     </div>
    <div class="back-button-container">
        <a href="Profile.html">Back</a>
    </div>

    <form action="" method="post" enctype="multipart/form-data">
        <div class="container">
            <div class="profile-container">
                <div class="profile-image-container">
                    <img id="profileImagePreview" src="<?php echo isset($user['profile_image']) ? 'profile_images/' . $user['profile_image'] : 'path/to/default/image.jpg'; ?>" alt="Profile Image">
                    <input type="file" id="profileImageInput" name="update_profile_image" style="display: none;">
                    <label for="profileImageInput" class="add-icon">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>
                <label for="update_username">Username:</label>
                <input type="text" id="update_username" name="update_username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                <label for="update_gender">Gender:</label>
                <select id="update_gender" name="update_gender" required>
                    <option value="Male" <?php if($user['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if($user['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                </select>
                <label for="update_email">Email:</label>
                <input type="email" id="update_email" name="update_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <label for="update_year_of_birth">Year of Birth:</label>
                <input type="date" id="update_year_of_birth" name="update_year_of_birth" value="<?php echo htmlspecialchars($user['year_of_birth']); ?>" required>
                <label for="new_pass">New Password:</label>
                <input type="password" id="new_pass" name="new_pass">
                <label for="confirm_pass">Confirm New Password:</label>
                <input type="password" id="confirm_pass" name="confirm_pass">
            </div>
            <div class="survey-container">
                <label for="update_age">Age:</label>
                <input type="number" id="update_age" name="update_age" value="<?php echo htmlspecialchars($survey['age']); ?>">
                <label for="weight">Weight:</label>
                <input type="number" id="weightInput" name="update_weight" min="0" max="200" step="1" value="<?php echo htmlspecialchars($survey['weight']); ?>">
                <label for="height">Height:</label>
                <input type="number" id="heightInput" name="update_height" min="0" max="300" step="1" value="<?php echo htmlspecialchars($survey['height']); ?>">
                <label for="update_fitness_goal">Fitness Goal:</label>
                <input type="text" id="update_fitness_goal" name="update_fitness_goal" value="<?php echo htmlspecialchars($survey['fitness_goal']); ?>">
                <label for="update_health">Health:</label>
                <div id="update_health">
                    <div>
                        <input type="radio" id="health_above_average" name="update_health" value="Above Average" <?php if($survey['health'] == 'Above Average') echo 'checked'; ?>>
                        <label for="health_above_average">Above Average</label>
                    </div>
                    <div>
                        <input type="radio" id="health_average" name="update_health" value="Average" <?php if($survey['health'] == 'Average') echo 'checked'; ?>>
                        <label for="health_average">Average</label>
                    </div>
                    <div>
                        <input type="radio" id="health_below_average" name="update_health" value="Below Average" <?php if($survey['health'] == 'Below Average') echo 'checked'; ?>>
                        <label for="health_below_average">Below Average</label>
                    </div>
                </div>
                <label for="update_gym">Preferred Gym:</label>
                <div id="update_gym">
                    <div>
                        <input type="radio" id="gym_yes" name="update_gym" value="Yes" <?php if($survey['gym'] == 'Yes') echo 'checked'; ?>>
                        <label for="gym_yes">Yes</label>
                    </div>
                    <div>
                        <input type="radio" id="gym_no" name="update_gym" value="No" <?php if($survey['gym'] == 'No') echo 'checked'; ?>>
                        <label for="gym_no">No</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-submit">
            <input type="submit" value="Update">
        </div>
    </form>
</div>

<script>
    let btn = document.querySelector("#btn");
    let sidebar = document.querySelector(".sidebar");

    btn.onclick = function() {
        sidebar.classList.toggle("active");
    }

    document.getElementById('profileImageInput').addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileImagePreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>
