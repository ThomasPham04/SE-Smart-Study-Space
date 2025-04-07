// Function to load components (header and footer)
function loadComponents() {
    // Get the current path
    const currentPath = window.location.pathname;
    const isInViews = currentPath.includes('/views/');
    const isUserPage = currentPath.includes('dashboard.html');
    
    // Adjust paths based on current location
    const basePath = isInViews ? '../components/' : 'components/';
    
    // Load appropriate header based on page type
    const headerFile = isUserPage ? 'header-user.html' : 'header.html';
    
    // Remove any existing header and footer
    const existingHeader = document.querySelector('nav.navbar');
    const existingFooter = document.querySelector('footer');
    if (existingHeader) existingHeader.remove();
    if (existingFooter) existingFooter.remove();
    
    // Load header
    fetch(basePath + headerFile)
        .then(response => response.text())
        .then(data => {
            document.querySelector('body').insertAdjacentHTML('afterbegin', data);
            // Initialize Bootstrap dropdowns after loading
            if (isUserPage) {
                const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
                const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl));
            }
        })
        .catch(error => console.error('Error loading header:', error));

    // Load footer
    fetch(basePath + 'footer.html')
        .then(response => response.text())
        .then(data => {
            document.querySelector('body').insertAdjacentHTML('beforeend', data);
        })
        .catch(error => console.error('Error loading footer:', error));
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', loadComponents); 