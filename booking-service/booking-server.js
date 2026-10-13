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

app.post('/new-booking', async (req, res) => {

    const { customerName, service } = req.body;

    const booking = {
        id: 101,
        customerName,
        service,
        status: "created",
    };

    try {
        const response = await fetch(
            "http://localhost:3001/new-notifications",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    bookingId: booking.id,
                    message: `New booking created for ${customerName}`,
                }),
            }
        );

        if (!response.ok) {
            throw new Error(
                `Notification service returned ${response.status}`
            );
        }

        const notification = await response.json();

        res.status(201).json({
            success: true,
            booking,
            notification,
        });

    } catch (error) {
        console.error("Notification request failed:", error.message);

        res.status(502).json({
            success: false,
            message: "Booking created, but notification failed",
        });
    }

});


app.listen(PORT, () => {
    console.log(`Booking service running on port ${PORT}`);
})
