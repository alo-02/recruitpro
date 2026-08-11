/**
 * RecruitPro - Authentication tab switching
 */
function switchTab(tab) {
    const loginForm = document.getElementById('form-login');
    const signupForm = document.getElementById('form-signup');
    const tabLogin = document.getElementById('tab-login');
    const tabSignup = document.getElementById('tab-signup');

    if (!loginForm || !signupForm) return;

    if (tab === 'login') {
        loginForm.classList.remove('d-none');
        signupForm.classList.add('d-none');
        tabLogin?.classList.add('active');
        tabSignup?.classList.remove('active');
    } else {
        signupForm.classList.remove('d-none');
        loginForm.classList.add('d-none');
        tabSignup?.classList.add('active');
        tabLogin?.classList.remove('active');
    }
}
