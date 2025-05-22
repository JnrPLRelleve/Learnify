$(document).ready(function() {
    $("form").on("submit", function(e) {
        e.preventDefault();
        $.ajax({
            url: "signup_Instructor.php",
            method: "POST",
            data: $(this).serialize() + "&ajax=true",
            success: function(response) {
                try {
                    const res = JSON.parse(response);
                    alert(res.message);
                    if (res.status === "success") {
                        window.location.href = "instructor_dashboard.php";
                    }
                } catch (e) {
                    console.error("Error parsing response:", e);
                    alert("An unexpected error occurred.");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", error);
                alert("An error occurred while processing the request.");
            }
        });
    });
});