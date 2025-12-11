<?php
session_start();
if(!isset($_SESSION["username"])){
    header("Location: index.php");
    exit();
}

require "user.php";
$user = new User();

$users = json_decode(file_get_contents("users.json"), true);
$currentUser = null;
foreach($users as $userData) {
    if($userData['username'] === $_SESSION['username']) {
        $currentUser = $userData;
        break;
    }
}


if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_account"])) {

    $newUsers = [];
    foreach($users as $userData) {
        if($userData['username'] !== $_SESSION['username']) {
            $newUsers[] = $userData;
        } else {
     
            if(isset($userData['profile_picture']) && 
               $userData['profile_picture'] !== 'default-avatar.png' &&
               file_exists($userData['profile_picture'])) {
                unlink($userData['profile_picture']);
            }
        }
    }
    
 
    file_put_contents("users.json", json_encode($newUsers, JSON_PRETTY_PRINT));
    
   
    session_destroy();
    header("Location: index.php?message=Account+deleted+successfully");
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_picture"])) {
    $uploadDir = "uploads/";
    if(!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $filename = $_SESSION['username'] . "_" . time() . "_" . basename($_FILES["profile_picture"]["name"]);
    $targetFile = $uploadDir . $filename;
    
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    
    if(in_array($imageFileType, $allowedTypes)) {
        if(move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
           
            if(isset($currentUser['profile_picture']) && 
               $currentUser['profile_picture'] !== 'default-avatar.png' &&
               file_exists($currentUser['profile_picture'])) {
                unlink($currentUser['profile_picture']);
            }
            
            foreach($users as &$u) {
                if($u['username'] === $_SESSION['username']) {
                    $u['profile_picture'] = $targetFile;
                    break;
                }
            }
            file_put_contents("users.json", json_encode($users, JSON_PRETTY_PRINT));
            $currentUser['profile_picture'] = $targetFile;
            $uploadMessage = "Profile picture updated successfully!";
        } else {
            $uploadError = "Error uploading file.";
        }
    } else {
        $uploadError = "Only JPG, JPEG, PNG & GIF files are allowed.";
    }
}


if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_profile_picture"])) {
    if(isset($currentUser['profile_picture']) && 
       $currentUser['profile_picture'] !== 'default-avatar.png' &&
       file_exists($currentUser['profile_picture'])) {
        
        unlink($currentUser['profile_picture']);
        
        foreach($users as &$u) {
            if($u['username'] === $_SESSION['username']) {
                $u['profile_picture'] = 'default-avatar.png';
                break;
            }
        }
        file_put_contents("users.json", json_encode($users, JSON_PRETTY_PRINT));
        $currentUser['profile_picture'] = 'default-avatar.png';
        $uploadMessage = "Profile picture deleted successfully!";
    }
}


if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    $fullname = $_POST["fullname"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $dob = $_POST["dob"];
    $pob = $_POST["pob"];
    $gender = $_POST["gender"];
    $email = $_POST["email"];
    
    foreach($users as &$u) {
        if($u['username'] === $_SESSION['username']) {
            $u['fullname'] = $fullname;
            $u['phone'] = $phone;
            $u['address'] = $address;
            $u['dob'] = $dob;
            $u['pob'] = $pob;
            $u['gender'] = $gender;
            $u['email'] = $email;
            break;
        }
    }
    file_put_contents("users.json", json_encode($users, JSON_PRETTY_PRINT));
    $currentUser = array_merge($currentUser, [
        'fullname' => $fullname,
        'phone' => $phone,
        'address' => $address,
        'dob' => $dob,
        'pob' => $pob,
        'gender' => $gender,
        'email' => $email
    ]);
    $updateMessage = "Profile updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="edit.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

.delete-account-section {
    margin-top: 265px;
    padding: 25px;
    text-align: center;
}

.delete-account-section h3 {
    color: #e74c3c;
    margin-bottom: 15px;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.warning-text {
    color: #e74c3c;
    font-size: 14px;
    margin-bottom: 20px;
    line-height: 1.5;
    padding: 0 10px;
}

.delete-account-form {
    margin-top: 15px;
}

.delete-account-btn {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    width: 100%;
    max-width: 300px;
    margin: 0 auto;
}

.delete-account-btn:hover {
    background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(231, 76, 60, 0.3);
}

.delete-account-btn:active {
    transform: translateY(0);
}


.delete-button-wrapper {
    margin-top: 10px;
    width: 100%;
}

.delete-btn {
    width: 100%;
    background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    margin-top: 5px;
}

.delete-btn:hover {
    background: linear-gradient(135deg, #ff4b2b 0%, #ff416c 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 75, 43, 0.3);
}

.delete-btn:active {
    transform: translateY(0);
}


    </style>
</head>
<body>
    <div class="dashboard-container">
     
        <header class="dashboard-header">
            <div class="header-content">
                <h1><i class="fas fa-user-circle"></i> Employee Profile</h1>
                <div class="header-actions">
                    <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </header>

        <div class="dashboard-content">
           
            <section class="profile-section">
                <div class="profile-card">

                    <div class="profile-picture-section">
                        <div class="profile-picture-container">
                            <img src="<?php echo isset($currentUser['profile_picture']) && file_exists($currentUser['profile_picture']) ? $currentUser['profile_picture'] : 'uploads/default.jpg'; ?>" 
                                 alt="Profile Picture" class="profile-img" id="profileImage">
                            
                            <form method="POST" action="edit.php" enctype="multipart/form-data" class="upload-form">
                                <div class="file-input-wrapper">
                                    <label for="profile_picture" class="file-label">
                                        <i class="fas fa-camera"></i> Change Photo
                                    </label>
                                    <input type="file" name="profile_picture" id="profile_picture" 
                                           accept="image/*" class="file-input" onchange="previewImage(event)">
                                    <button type="submit" class="upload-btn">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                </div>
                                
                                
                                
                                <small class="file-note">Max size: 5MB (JPG, PNG, GIF)</small>
                            </form>
                            
                            <?php if(isset($uploadMessage)): ?>
                                <div class="message success"><?php echo $uploadMessage; ?></div>
                            <?php endif; ?>
                            <?php if(isset($uploadError)): ?>
                                <div class="message error"><?php echo $uploadError; ?></div>
                            <?php endif; ?>
                            <div class="delete-account-section">
                            <form method="POST" action="edit.php" class="delete-account-form">
                                <input type="hidden" name="delete_account" value="1">
                                <button type="submit" class="delete-account-btn" onclick="return confirmDeleteAccount()">
                                    <i class="fas fa-user-slash"></i> Delete My Account
                                </button>
                            </form>
                        </div>
                        </div>
                    </div>

                   
                    <div class="profile-info-section">
                        <h2 class="profile-title">Personal Information</h2>
                        
                        <?php if(isset($updateMessage)): ?>
                            <div class="message success"><?php echo $updateMessage; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="dashboard.php" class="profile-form">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fullname">
                                        <i class="fas fa-user"></i> Full Name
                                    </label>
                                    <input type="text" id="fullname" name="fullname" 
                                           value="<?php echo htmlspecialchars($currentUser['fullname'] ?? ''); ?>" 
                                           placeholder="Enter your full name" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope"></i> Email Address
                                    </label>
                                    <input type="email" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" 
                                           placeholder="Enter your email" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">
                                        <i class="fas fa-phone"></i> Phone Number
                                    </label>
                                    <input type="text" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" 
                                           placeholder="+63 XXX-XXX-XXXX" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="dob">
                                        <i class="fas fa-calendar"></i> Date of Birth
                                    </label>
                                    <input type="date" id="dob" name="dob" 
                                           value="<?php echo htmlspecialchars($currentUser['dob'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="pob">
                                        <i class="fas fa-map-marker-alt"></i> Place of Birth
                                    </label>
                                    <input type="text" id="pob" name="pob" 
                                           value="<?php echo htmlspecialchars($currentUser['pob'] ?? ''); ?>" 
                                           placeholder="City, Province" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="gender">
                                        <i class="fas fa-venus-mars"></i> Gender
                                    </label>
                                    <select id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo ($currentUser['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($currentUser['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($currentUser['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="address">
                                        <i class="fas fa-home"></i> Address
                                    </label>
                                    <textarea id="address" name="address" rows="3" 
                                              placeholder="Enter your complete address" required><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="update-btn">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <a href="dashboard.php" class="back-btn">
                                    <i class="fas fa-home"></i> Back to Home
                                </a>
                            </div>
                        </form>
                        
                   
                        
                    </div>
                </div>
            </section>
        </div>
        
        <footer class="dashboard-footer">
            <p>&copy; 2025 Employee Profile System</p>
        </footer>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('profileImage');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
        
        function confirmDeleteAccount() {
            const username = "<?php echo $_SESSION['username']; ?>";
            return confirm(`⚠️ WARNING: This action cannot be undone!\n\nAre you absolutely sure you want to delete your account "${username}"?\n\nAll your profile information, pictures, and data will be permanently deleted.`);
        }
      
        setTimeout(function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s';
                setTimeout(() => message.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>