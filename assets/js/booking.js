document.addEventListener("DOMContentLoaded", function () {
    const dateInput = document.getElementById("appointment_date");
    const timeSlotsDiv = document.getElementById("timeSlots");
    const startTimeInput = document.getElementById("start_time");
    const bookingForm = document.getElementById("bookingForm");

    function loadTimeSlots() {
        const appointmentDate = dateInput.value;

        timeSlotsDiv.innerHTML = "";
        startTimeInput.value = "";

        if (!appointmentDate) {
            timeSlotsDiv.innerHTML = '<p class="empty-slot-text">Please select a date to view time slots.</p>';
            return;
        }

        fetch("get_slots.php?appointment_date=" + encodeURIComponent(appointmentDate))
            .then(response => response.json())
            .then(data => {
                timeSlotsDiv.innerHTML = "";

                if (!Array.isArray(data) || data.length === 0) {
                    timeSlotsDiv.innerHTML = '<p class="empty-slot-text">No time slots available.</p>';
                    return;
                }

                data.forEach(slot => {
                    const slotBox = document.createElement("button");
                    slotBox.type = "button";
                    slotBox.classList.add("time-slot-box", slot.status);
                    slotBox.textContent = slot.label;

                    if (slot.status === "available") {
                        slotBox.addEventListener("click", function () {
                            document.querySelectorAll(".time-slot-box").forEach(el => {
                                el.classList.remove("selected");
                            });

                            slotBox.classList.add("selected");
                            startTimeInput.value = slot.start_time;
                        });
                    } else if (slot.status === "booked") {
                        slotBox.addEventListener("click", function () {
                            alert("This time is already booked.");
                        });
                    } else if (slot.status === "blocked") {
                        slotBox.addEventListener("click", function () {
                            alert("This time slot is blocked.");
                        });
                    }

                    timeSlotsDiv.appendChild(slotBox);
                });
            })
            .catch(error => {
                console.error("Error loading time slots:", error);
                timeSlotsDiv.innerHTML = '<p class="empty-slot-text">Error loading time slots.</p>';
            });
    }

    dateInput.addEventListener("change", loadTimeSlots);

    bookingForm.addEventListener("submit", function (e) {
        if (!startTimeInput.value) {
            e.preventDefault();
            alert("Please select a time slot.");
        }
    });

    const today = new Date().toISOString().split("T")[0];
    dateInput.min = today;
});