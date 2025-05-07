export const setActive = () => {
  const currentPath = window.location.pathname.split(".php")[0];
  const navLinks = document.querySelectorAll(".sidebar ul li a");

  navLinks.forEach((link) => {
    const linkPath = new URL(link.href, window.location.origin).pathname;

    if (linkPath.includes(currentPath) && currentPath !== "/") {
      link.classList.add("active");
      link.parentElement.classList.add("active");
    } else if (linkPath === "/index.php" && currentPath === "/") {
      link.classList.add("active");
      link.parentElement.classList.add("active");
    }
  });
};
