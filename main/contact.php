<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>

    <style>
        /* General Page Styling */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h2, h3 {
            text-align: center;
            color: #333;
        }

        /* Form Styling */
        form {
            background-color: white;
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            max-width: 600px;
            margin: 20px auto;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            padding: 10px 20px;
            font-size: 14px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Contact Info Section Styling */
        .contact-info {
            text-align: center;
            margin-top: 40px;
        }

        .contact-info p {
            font-size: 16px;
            color: #333;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/portions/header.php'; ?> <!-- Include navigation -->

<h2>Contact Us</h2>

<p>If you have any inquiries, suggestions, or need support, please don't hesitate to contact us. We're here to help!</p>

<form action="submit_contact.php" method="POST">
    <label for="name">Your Name:</label><br>
    <input type="text" id="name" name="name" required><br><br>

    <label for="email">Your Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>

    <label for="message">Your Message:</label><br>
    <textarea id="message" name="message" rows="4" required></textarea><br><br>

    <button type="submit">Send Message</button>
</form>
<?php
include __DIR__ . '/../database/db.php'; // Adjust the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Prepare SQL statement to insert the contact message into the database
    $sql = "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        // Redirect or display a success message
        echo "<p>Your message has been sent successfully! We'll get back to you shortly.</p>";
    } else {
        // Display an error message
        echo "<p>Failed to send the message. Please try again later.</p>";
    }

    $stmt->close();
}

$conn->close(); // Close the database connection
?>


<div class="contact-info">
    <h3>Our Address:</h3>
    <p>BrightStart Academy, 123 Education Blvd, Amman, Jordan</p>

    <h3>Phone:</h3>
    <p>+962-123-4567</p>

    <h3>Email:</h3>
    <p>contact@brightstartacademy.com</p>
</div>

<?php include __DIR__ . '/portions/footer.php'; ?> <!-- Include footer -->

</body>
</html>
