// Function to load components (header and footer)
function loadComponents() {
    // Get the current path
    const currentPath = window.location.pathname;
    const isInViews = currentPath.includes('/views/');
    
    // Adjust paths based on current location
    const basePath = isInViews ? '../components/' : 'components/';
    
    // Load header
    fetch(basePath + 'header.html')
        .then(response => response.text())
        .then(data => {
            document.querySelector('body').insertAdjacentHTML('afterbegin', data);
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