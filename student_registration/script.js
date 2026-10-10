
document.getElementById("registrationForm")
.addEventListener("submit", function(event) {

    event.preventDefault();

    document.querySelectorAll(".error").forEach(function(error) {
        error.textContent = "";
    });

    document.getElementById("success").textContent = "";

    let valid = true;

    let name = document.getElementById("name").value.trim();
    let roll = document.getElementById("roll").value.trim();
    let email = document.getElementById("email").value.trim();
    let course = document.getElementById("course").value;
    let password = document.getElementById("password").value;
    let file = document.getElementById("document").files[0];

    // Validate student name
    if (name === "") {
        document.getElementById("nameError").textContent =
            "Student name is required.";
        valid = false;
    }

    // Validate roll number
    if (roll === "") {
        document.getElementById("rollError").textContent =
            "Roll number is required.";
        valid = false;
    }

    // Validate email
    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {
        document.getElementById("emailError").textContent =
            "Enter a valid email address.";
        valid = false;
    }

    // Validate course
    if (course === "") {
        document.getElementById("courseError").textContent =
            "Please select a course.";
        valid = false;
    }

    // Validate password
    let passwordPattern =
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

    if (!passwordPattern.test(password)) {
        document.getElementById("passwordError").textContent =
            "Use at least 8 characters with uppercase, lowercase, number and symbol.";
        valid = false;
    }

    // Validate uploaded file
    if (!file) {
        document.getElementById("fileError").textContent =
            "Please upload a document.";
        valid = false;
    } else {
        let allowedTypes = [
            "application/pdf",
            "image/jpeg",
            "image/png"
        ];

        if (!allowedTypes.includes(file.type)) {
            document.getElementById("fileError").textContent =
                "Upload only PDF, JPG or PNG files.";
            valid = false;
        } else if (file.size > 2 * 1024 * 1024) {
            document.getElementById("fileError").textContent =
                "File size must be below 2 MB.";
            valid = false;
        }
    }

    // Display result
    if (valid) {
        document.getElementById("success").textContent =
            "Registration form validated successfully!";

        this.reset();
    }
});

