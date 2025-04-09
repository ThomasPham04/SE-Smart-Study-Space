function loadComponents() {
    // Dynamically inject <base href> depending on where it's hosted
    const isGithubPages = window.location.hostname === 'thomaspham04.github.io';
    const base = document.createElement('base');
    base.href = isGithubPages ? '/SE-Smart-Study-Space/' : '/';
    document.head.prepend(base);

    const currentPath = window.location.pathname;
    const isInViews = currentPath.includes('/views/');
    const isUserPage = currentPath.includes('dashboard.html');

    const basePath = isInViews ? '../components/' : 'components/';
    const headerFile = isUserPage ? 'header-user.html' : 'header.html';

    const existingHeader = document.querySelector('nav.navbar');
    const existingFooter = document.querySelector('footer');
    if (existingHeader) existingHeader.remove();
    if (existingFooter) existingFooter.remove();

    // Load header
    fetch(basePath + headerFile)
        .then(response => response.text())
        .then(data => {
            document.querySelector('body').insertAdjacentHTML('afterbegin', data);
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

document.addEventListener('DOMContentLoaded', loadComponents);
