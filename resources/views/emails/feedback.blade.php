<!DOCTYPE html>
<html>
<head>
    <title>Contact Form Submission</title>
</head>
<body>
    <h2>Feedback Form Submission</h2>

    <p>Name: {{ $data['name'] }}</p>
    <p>Email: {{ $data['email'] }}</p>
    <p>Phone: {{ $data['phone'] }}</p>
    <p>Feedback: {{ $data['feedback'] }}</p>
    <p>Rating: {{ $data['rating'] }}</p>
</body>
</html>
