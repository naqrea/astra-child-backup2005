document.addEventListener("DOMContentLoaded", function () {
    const scrollBtn = document.querySelector(".view-experiences-btn");
    const targetSection = document.querySelector("h2.store-product-list-title");

    if (scrollBtn && targetSection) {
        scrollBtn.addEventListener("click", function (e) {
            e.preventDefault();
            targetSection.scrollIntoView({ behavior: "smooth" });
        });
    }
});