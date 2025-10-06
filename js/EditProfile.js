const firstname = document.getElementById("firstname");
const lastname = document.getElementById("lastname");
const email = document.getElementById("email");
const phone = document.getElementById("phone");
const zip = document.getElementById("zip");
const city = document.getElementById("city");
const address = document.getElementById("address");

const editBtn = document.getElementById("editBtn");
const logoutBtn = document.getElementById("logoutBtn");
const cancelBtn = document.getElementById("cancelBtn");
const passBtn = document.getElementById("passBtn");

cancelBtn.style.display = "none";
passBtn.style.display = "none";

// To store original values before editing
let originalValues = {};

editBtn.addEventListener("click", function () {
    // Save original values
    originalValues = {
        firstname: firstname.value,
        lastname: lastname.value,
        email: email.value,
        phone: phone.value,
        zip: zip.value,
        city: city.value,
        address: address.value
    };

    // Remove readonly
    [firstname, lastname, email, phone, zip, city, address].forEach(input => {
        input.removeAttribute("readonly");
    });

    // Toggle buttons
    editBtn.style.display = "none";
    logoutBtn.style.display = "none";
    cancelBtn.style.display = "block";
    passBtn.style.display = "block";
});

cancelBtn.addEventListener("click", function (e) {
    e.preventDefault();

    // Restore readonly
    [firstname, lastname, email, phone, zip, city, address].forEach(input => {
        input.setAttribute("readonly", true);
    });

    // Restore original values
    firstname.value = originalValues.firstname;
    lastname.value = originalValues.lastname;
    email.value = originalValues.email;
    phone.value = originalValues.phone;
    zip.value = originalValues.zip;
    city.value = originalValues.city;
    address.value = originalValues.address;

    // Toggle buttons back
    editBtn.style.display = "block";
    logoutBtn.style.display = "block";
    cancelBtn.style.display = "none";
    passBtn.style.display = "none";
});

document.getElementById("logoutBtn").addEventListener("click", function () {
  Swal.fire({
    title: "Are you sure?",
    text: "You will be logged out of your account.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, logout",
    cancelButtonText: "Stay"
  }).then((result) => {
    if (result.isConfirmed) {
      // Redirect to logout page
      window.location.href = "functions/LogoutFunction.php";
    }
  });
});