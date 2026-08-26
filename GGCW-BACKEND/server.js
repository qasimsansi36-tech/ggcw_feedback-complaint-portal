const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');

const app = express();
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Login Endpoint
app.post('/api/auth/login', (req, res) => {
    const { email, password, role } = req.body;
    console.log(`Login Request Received for: ${email}`);
    
    res.json({
        success: true,
        role: role || 'student',
        user: { name: "Authorized User", email: email, is_approved: true }
    });
});

// Register Endpoint
app.post('/api/auth/register', (req, res) => {
    console.log("Registration Request Received");
    res.json({
        success: true,
        message: "User registered successfully!"
    });
});

// Reset Password Endpoint
app.post('/api/auth/reset-password', (req, res) => {
    res.json({
        success: true,
        message: "Password reset successful!"
    });
});

const PORT = 8000;
app.listen(PORT, () => {
    console.log("Backend Server running on http://127.0.0.1:8000");
});