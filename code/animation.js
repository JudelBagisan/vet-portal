const observer = new IntersectionObserver((entries) =>{
    entries.forEach((entry) => {
        console.log(entry)
        if (entry.isIntersecting) {
            entry.target.classList.add('show');
        }else {
            entry.target.classList.remove('show');
        }
    });
});

const hiddenElements = document.querySelectorAll('.hide');
hiddenElements.forEach((el)=> observer.observe(el));


const petModal = document.querySelector('#addPetModal');
  function togglePetModal() {
    if(petModal.style.display === 'none' || petModal.style.display == '') {
      petModal.style.display = 'flex';
    }
    else {
      petModal.style.display = 'none';
    }
  }