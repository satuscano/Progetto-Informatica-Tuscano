// Funzione per aprire/chiudere il menu
function toggleMenu() {
    document.getElementById('menuPaziente').classList.toggle('hidden');
    document.body.classList.toggle('menu-open');
}

// Funzione per aprire/chiudere il menu quando si clicca su Modfica Profilo
function toggleEdit(id) {
    document.getElementById(id).classList.toggle("hidden");
}