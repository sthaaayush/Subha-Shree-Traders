let navbar = document.querySelector(".header .flex .navbar");
let profile = document.querySelector(".header .flex .profile");

document.querySelector("#menu-btn").onclick = () => {
   navbar.classList.toggle("active");
   profile.classList.remove("active");
};

document.querySelector("#user-btn").onclick = () => {
   profile.classList.toggle("active");
   navbar.classList.remove("active");
};

window.onscroll = () => {
   navbar.classList.remove("active");
   profile.classList.remove("active");
};

let mainImage = document.querySelector(
   ".quick-view .box .row .image-container .main-image img"
);
let subImages = document.querySelectorAll(
   ".quick-view .box .row .image-container .sub-image img"
);

subImages.forEach((images) => {
   images.onclick = () => {
      src = images.getAttribute("src");
      mainImage.src = src;
   };
});

// Get the necessary elements
const paymentMethodSelect = document.getElementById("paymentMethod");
const onlinePaymentContainer = document.getElementById(
   "onlinePaymentContainer"
);
const cancelOnlinePaymentBtn = document.getElementById("cancelOnlinePayment");

// Function to show or hide the online payment container based on selection
function toggleOnlinePayment() {
   if (paymentMethodSelect.value === "online") {
      onlinePaymentContainer.style.display = "block";
   } else {
      onlinePaymentContainer.style.display = "none";
   }
}

// Event listener for changes in the payment method dropdown
paymentMethodSelect.addEventListener("change", toggleOnlinePayment);

// Event listener for the cancel button
cancelOnlinePaymentBtn.addEventListener("click", function () {
   onlinePaymentContainer.style.display = "none";
   // Optionally, you can also reset the payment method dropdown to its initial value
   paymentMethodSelect.value = "cash on delivery";
});
