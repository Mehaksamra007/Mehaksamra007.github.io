<?php
require_once 'auth_functions.php';
require_once 'config.php';

// Get all active job postings
$jobs = [];
$sql = "SELECT * FROM job_postings WHERE is_active = TRUE ORDER BY posted_date DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}

// Get categories for footer
$categories = [];
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Process form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = intval($_POST['job_id']);
    $full_name = $conn->real_escape_string(trim($_POST['full_name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $cover_letter = $conn->real_escape_string(trim($_POST['cover_letter']));

    // Validate inputs
    if (empty($full_name) || empty($email) || empty($phone) || empty($_FILES['resume']['name'])) {
        $error_message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Handle file upload
        $upload_dir = 'uploads/resumes/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['resume']['name']);
        $target_file = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if file is a PDF or DOC/DOCX
        $allowed_types = ['pdf', 'doc', 'docx'];
        if (!in_array($file_type, $allowed_types)) {
            $error_message = "Only PDF, DOC, and DOCX files are allowed.";
        } elseif ($_FILES['resume']['size'] > 2000000) { // 2MB limit
            $error_message = "File size must be less than 2MB.";
        } elseif (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
            // Insert into database
            $sql = "INSERT INTO job_applications (job_id, full_name, email, phone, resume_path, cover_letter) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssss", $job_id, $full_name, $email, $phone, $target_file, $cover_letter);
            
            if ($stmt->execute()) {
                $success_message = "Thank you for your application! We'll review your materials and get back to you soon.";
                // Reset form values
                $_POST = array();
            } else {
                $error_message = "There was an error submitting your application. Please try again.";
                // Delete the uploaded file if DB insertion failed
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
            }
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers - Fresh Fields</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
            --danger: #e74c3c;
            --warning: #f39c12;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 6px 12px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }

        /* Header Styles */
        header {
            background: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--dark);
        }

        .logo img {
            height: 40px;
        }

        .logo span {
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 0;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .btn-signin {
            background: none;
            border: none;
            color: var(--dark);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-signin:hover {
            color: var(--primary);
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            color: var(--dark);
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .cart-icon:hover {
            color: var(--primary);
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        /* Main Content */
        main {
            margin-top: 80px;
        }


        .page-header {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/Background.jpg');
            background-size: cover;
            background-position: center;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: var(--dark);
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .section-description {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 3rem;
            color: var(--gray);
            font-size: 1.1rem;
            padding: 0 2rem;
        }

        /* Job Listings */
        .job-listings {
            margin-bottom: 3rem;
        }

        .job-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .job-card h3 {
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .job-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
            color: var(--gray);
            font-size: 0.9rem;
            flex-wrap: wrap;
        }

        .job-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .job-meta i {
            font-size: 1rem;
        }

        .job-description {
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .job-description h4 {
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
            color: var(--dark);
        }

        .job-description p {
            margin-bottom: 1rem;
            line-height: 1.7;
        }

        .btn {
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-block;
            text-decoration: none;
        }

        .btn:hover {
            background: var(--primary-dark);
            box-shadow: var(--shadow-hover);
        }

        /* Application Form */
        .application-form {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            display: none;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .application-form.active {
            display: block;
        }

        .application-form h2 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .application-form h2 span {
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .file-input-label {
            display: block;
            padding: 1.5rem;
            border: 2px dashed #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            background: #f9f9f9;
        }

        .file-input-label:hover {
            border-color: var(--primary);
            background: rgba(46, 204, 113, 0.05);
        }

        .file-input-label i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .file-input-label div {
            font-weight: 500;
            color: var(--dark);
        }

        .file-input {
            display: none;
        }

        .file-name {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--gray);
            text-align: center;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .alert i {
            font-size: 1.5rem;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            color: var(--primary);
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 4rem 2rem 2rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .footer-section p,
        .footer-section a {
            color: #bdc3c7;
            text-decoration: none;
            line-height: 1.8;
            transition: var(--transition);
            display: block;
            margin-bottom: 0.5rem;
        }

        .footer-section a:hover {
            color: var(--primary);
            padding-left: 5px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #bdc3c7;
            font-size: 0.9rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            color: white;
            background: rgba(255,255,255,0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }


        /* Responsive */
        @media (max-width: 992px) {
            .page-header h1 {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 768px) {
            nav {
                padding: 0 1rem;
            }

            .nav-links {
                display: none;
            }

            .page-header {
                height: 250px;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .page-header p {
                font-size: 1rem;
            }
            
            .job-meta {
                gap: 1rem;
            }
            
            .application-form {
                padding: 1.5rem;
            }

        }
.user-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.welcome-msg {
    color: var(--dark);
    font-weight: 500;
}

.btn-logout {
    background: none;
    border: none;
    color: var(--danger);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    font-size: inherit;
    font-family: inherit;
    padding: 0;
}

.btn-logout:hover {
    color: var(--primary);
}

        @media (max-width: 576px) {
            .page-header {
                height: 200px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .job-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .job-card {
                padding: 1.5rem;
            }
            
            .application-form {
                padding: 1rem;
            }
            
            .application-form h2 {
                font-size: 1.5rem;
            }

        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <a href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
                        <img src="images/logo.png" alt="Fresh Fields Logo">
                        Fresh<span>Fields</span>
                    </a>
                </div>
                
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="product.php">Products</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="career.php" class="active">Careers</a></li>
                </ul>
                
                <div class="nav-actions">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <span class="welcome-msg">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
                            <a href="logout.php" class="btn-logout">Logout</a>
                        </div>
                    <?php else: ?>
                        <button class="btn-signin" onclick="window.location.href='signin.php'">Sign In</button>
                    <?php endif; ?>
                    
                    <div class="cart-icon" onclick="window.location.href='cart.php'">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="page-header">
            <div>
                <h1>Join Our Team</h1>
                <p>Help us deliver fresh, quality groceries to our community</p>
            </div>
        </section>

        <div class="container">
            <h2 class="section-title">Grow With Fresh Fields</h2>
            <p class="section-description">At Fresh Fields, we're more than just a grocery store - we're a community of passionate individuals dedicated to bringing the freshest, highest quality products to our customers. We offer competitive compensation, flexible schedules, and opportunities for growth.</p>
            
            <section class="job-listings">
                <h2 class="section-title">Current Openings</h2>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div><?= htmlspecialchars($success_message) ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?= htmlspecialchars($error_message) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (count($jobs) > 0): ?>
                    <?php foreach ($jobs as $job): ?>
                    <div class="job-card" id="job-<?= $job['job_id'] ?>">
                        <h3><?= htmlspecialchars($job['title']) ?></h3>
                        <div class="job-meta">
                            <span><i class="fas fa-building"></i> <?= htmlspecialchars($job['department']) ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['location']) ?></span>
                            <?php if (!empty($job['salary_range'])): ?>
                                <span><i class="fas fa-money-bill-wave"></i> <?= htmlspecialchars($job['salary_range']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="job-description">
                            <h4>Job Description</h4>
                            <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                        </div>
                        <div class="job-description">
                            <h4>Requirements</h4>
                            <p><?= nl2br(htmlspecialchars($job['requirements'])) ?></p>
                        </div>
                        <button class="btn" onclick="showApplicationForm(<?= $job['job_id'] ?>, '<?= htmlspecialchars(addslashes($job['title'])) ?>')">
                            Apply Now
                        </button>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="job-card">
                        <p>We currently don't have any open positions. Please check back later!</p>
                    </div>
                <?php endif; ?>
            </section>

            <div class="application-form" id="application-form">
                <h2>Apply for: <span id="job-title"></span></h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="job_id" id="form-job-id">
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" name="phone" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Resume (PDF or Word) *</label>
                        <label for="resume" class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div>Click to upload your resume</div>
                            <div class="file-name" id="file-name">No file selected</div>
                        </label>
                        <input type="file" id="resume" name="resume" class="file-input" accept=".pdf,.doc,.docx" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cover_letter">Cover Letter (Optional)</label>
                        <textarea id="cover_letter" name="cover_letter" placeholder="Tell us why you'd be a great fit for this position..."><?= isset($_POST['cover_letter']) ? htmlspecialchars($_POST['cover_letter']) : '' ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn submit-btn">Submit Application</button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Fresh Fields</h3>
                    <p>We're committed to bringing you the freshest, highest quality groceries at affordable prices.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="index.php">Home</a>
                    <a href="product.php">Products</a>
                    <a href="contact.php">Contact</a>
                    <a href="about.php">About</a>
                    <a href="career.php">Careers</a>
                </div>
                <div class="footer-section">
                    <h3>Categories</h3>
                    <?php foreach ($categories as $category): 
                        $category_slug = strtolower(str_replace(' ', '-', $category['category_name']));
                    ?>
                    <a href="product.php?category=<?= $category_slug ?>"><?= $category['category_name'] ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-envelope"></i> info@freshfields.com</p>
                    <p><i class="fas fa-phone"></i> (437) 871-1007</p>
                    <p><i class="fas fa-map-marker-alt"></i> 20 Columbia St W, Waterloo, Ontario, N2L 3K3</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Fresh Fields. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Show application form for specific job
        function showApplicationForm(jobId, jobTitle) {
            document.getElementById('form-job-id').value = jobId;
            document.getElementById('job-title').textContent = jobTitle;
            document.getElementById('application-form').classList.add('active');
            
            // Scroll to form
            document.getElementById('application-form').scrollIntoView({
                behavior: 'smooth'
            });
        }
        
        // Display selected file name
        document.getElementById('resume').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
            document.getElementById('file-name').textContent = fileName;
        });
        
        // Close form when clicking outside
        document.addEventListener('click', function(e) {
            const form = document.getElementById('application-form');
            if (form.classList.contains('active') && !form.contains(e.target)) {
                // Check if the click was not on an apply button
                if (!e.target.classList.contains('btn') && !e.target.closest('.btn')) {
                    form.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>