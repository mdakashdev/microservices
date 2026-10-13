const express = require("express");

const app = express();
const PORT = 3001;

app.use(express.json());

app.get('/health-check', (req, res) => {
    res.json({
        service: "notification-service-test",
        status: 'ok'
    });
});

app.post('/new-notifications', (req, res) => {

    const { bookingId, message } = req.body;

    console.log("New notification received:", {
        bookingId,
        message,
    });

    res.status(200).json({
        success: true,
        message: "Notification received",
        bookingId,
    });
});

app.listen(PORT, () => {
    console.log(`Notification service running on port ${PORT}`);
});