
const express = require("express");

const app = express();
const PORT = 3001;

app.get("/health", (req, res) => {
    res.json({
        service: "notification-service",
        status: "OK",
    });
});


app.use(express.json());

app.post("/notifications", (req, res) => {
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
