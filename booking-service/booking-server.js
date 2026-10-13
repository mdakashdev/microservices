const express = require("express");

const app = express();
const PORT = 3000;

app.use(express.json());

app.get('/health-check', (req, res) => {
    res.json({
        service: "booking service test",
        status: "ok"
    });
});

app.post('/new-booking', (req, res) => {

    const { customerName, service } = req.body;

    const booking = {
        id: 101,
        customerName,
        service,
        status: "created",
    };

    res.status(200).json({
        success: true,
        booking
    });
});


app.listen(PORT, () => {
    console.log(`Booking service running on port ${PORT}`);
})
