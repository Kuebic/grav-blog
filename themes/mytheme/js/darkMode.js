// Check for OS-level dark mode preference
const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)").matches;

// Get the user's saved theme preference from localStorage
const currentTheme = localStorage.getItem("theme");

// Apply dark mode if the user prefers it or has previously selected it
if (currentTheme === "dark" || (currentTheme === null && prefersDarkScheme)) {
    document.body.classList.add("dark-mode");
}

// Add event listeners to all dark mode switcher buttons
const switches = document.querySelectorAll(".dark-mode-switcher");

switches.forEach((switcher) => {
    switcher.addEventListener("click", toggleDarkMode);
});

function toggleDarkMode(event) {
    event.preventDefault(); // Prevent default link behavior

    // Toggle the .dark-mode class on the body
    document.body.classList.toggle("dark-mode");

    // Save the user's preference in localStorage
    if (document.body.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }
}
