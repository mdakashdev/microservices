const express = require("express");

const app = express();
const PORT = 3001;

app.get('/health-check', (req, res) => {
    res.json({
        service: "notification-service-test",
        status: 'ok'
    });
});

app.listen(PORT, () => {
    console.log(`Notification service running on port ${PORT}`);
});