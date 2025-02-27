function changeLanguage() {
    const body = document.body;
    const languageButton = document.querySelector('.language-btn');
    
    if (body.classList.contains('english')) {
        body.classList.remove('english');
        body.classList.add('thai');
        languageButton.innerText = 'English';
    } else {
        body.classList.remove('thai');
        body.classList.add('english');
        languageButton.innerText = 'ภาษา';
    }
}
