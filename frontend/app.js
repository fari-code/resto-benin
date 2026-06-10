// États globaux de l'application
let panier = [];
let noteSelectionnee = 5;
let PLATS = [];

const AVIS_INITIALS = [
  { nom: "Anicet", texte: "Le ragoût d'igname est tout simplement un délice !", note: 5 },
  { nom: "Mariette", texte: "Très bon service, rapide et efficace.", note: 4 }
];

// Charger le panier depuis localStorage au démarrage
function chargerPanierDepuisLocalStorage() {
  const panierStocke = localStorage.getItem('restobenin_panier');
  if (panierStocke) {
    panier = JSON.parse(panierStocke);
    mettreAJourPanierVisuel();
  }
}

// Sauvegarder le panier dans localStorage
function sauvegarderPanier() {
  localStorage.setItem('restobenin_panier', JSON.stringify(panier));
}

document.addEventListener("DOMContentLoaded", () => {
  chargerPanierDepuisLocalStorage(); // Charger le panier sauvegardé
  chargerMenuDepuisBDD();
  initialiserAvis();
  initReservationForm();
  verifierCommandeActive(); // Vérifier s'il y a une commande en cours
});

// Vérifier une commande active depuis la BDD
function verifierCommandeActive() {
  const idCommande = localStorage.getItem('restobenin_commande_active');
  if (idCommande) {
    fetch(`../api/get_commande.php?id_commande=${idCommande}`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.commande && data.commande.statut !== 'livree') {
          // Afficher la commande en cours
          document.getElementById("suivi-id").innerText = `#${data.commande.id_commande}`;
          document.getElementById("suivi-vide").classList.add("hidden");
          document.getElementById("suivi-actif").classList.remove("hidden");
          mettreAJourSuivi(data.commande.statut);
        } else {
          // Commande terminée, on nettoie
          localStorage.removeItem('restobenin_commande_active');
        }
      })
      .catch(error => console.error("Erreur vérification commande:", error));
  }
}

function mettreAJourSuivi(statut) {
  const line = document.getElementById("progress-bar-line");
  const txt = document.getElementById("suivi-statut-texte");
  const s2 = document.getElementById("step-2");
  const s3 = document.getElementById("step-3");
  
  if (!line) return;
  
  switch(statut) {
    case 'en_attente':
      line.style.width = "0%";
      txt.innerText = "Commande reçue par le restaurant";
      if (s2) s2.className = "z-10 bg-white p-2 rounded-full border-2 border-gray-200";
      if (s3) { s3.className = "z-10 bg-white p-2 rounded-full border-2 border-gray-200"; s3.innerHTML = '<i class="fa-solid fa-bell text-gray-400 text-xs w-4 h-4 block"></i>'; }
      break;
    case 'en_cuisine':
      line.style.width = "50%";
      txt.innerText = "Le Chef prépare vos plats...";
      if (s2) s2.className = "z-10 bg-white p-2 rounded-full border-2 border-red-500 text-red-500";
      if (s3) s3.className = "z-10 bg-white p-2 rounded-full border-2 border-gray-200";
      break;
    case 'prete':
      line.style.width = "100%";
      txt.innerText = "Vos plats sont prêts ! À table !";
      if (s2) s2.className = "z-10 bg-white p-2 rounded-full border-2 border-red-500 text-red-500";
      if (s3) { s3.className = "z-10 bg-white p-2 rounded-full border-2 border-green-500 text-green-500"; s3.innerHTML = '<i class="fa-solid fa-check text-xs w-4 h-4 block"></i>'; }
      break;
    default:
      break;
  }
}

function chargerMenuDepuisBDD() {
  fetch("../api/get_plats.php")
    .then(response => response.json())
    .then(data => { 
      PLATS = data; 
      afficherMenu(PLATS);
      genererBoutonsCategories();
    })
    .catch(error => console.error("Erreur:", error));
}

function genererBoutonsCategories() {
  const container = document.getElementById("categories-container");
  if (!container) return;
  
  const categoriesUniques = [...new Set(PLATS.map(p => p.categorie))];
  
  container.innerHTML = '';
  
  const btnTous = document.createElement('button');
  btnTous.className = 'menu-tab active px-6 py-2 rounded-full font-medium shadow-sm border transition bg-red-500 text-white';
  btnTous.textContent = 'Tous';
  btnTous.onclick = () => filtrerMenu('tous');
  btnTous.id = 'tab-tous';
  container.appendChild(btnTous);
  
  categoriesUniques.forEach((cat) => {
    const btn = document.createElement('button');
    btn.className = 'menu-tab bg-white px-6 py-2 rounded-full font-medium shadow-sm border transition';
    btn.textContent = cat;
    btn.onclick = () => filtrerMenu(cat);
    btn.id = `tab-${cat.replace(/ /g, '-')}`;
    container.appendChild(btn);
  });
}

function afficherMenu(liste) {
  const grid = document.getElementById("menu-grid");
  grid.innerHTML = "";
  liste.forEach((p) => {
    grid.innerHTML += `<div class="bg-white rounded-2xl overflow-hidden shadow-md border hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
      <div class="relative w-full overflow-hidden bg-gray-200">
        <img src="${p.image || p.img || 'images/default.jpg'}" alt="${p.nom || p.titre}" class="w-full h-48 sm:h-56 md:h-64 object-cover transition duration-500 hover:scale-105">
      </div>
      <div class="p-5">
        <div class="flex justify-between items-start mb-2">
          <h4 class="font-bold text-lg">${p.nom || p.titre}</h4>
          <span class="text-red-500 font-bold">${parseFloat(p.prix).toFixed(2)} FCFA</span>
        </div>
        <p class="text-gray-500 text-sm mb-4">${p.description || p.desc}</p>
        <button onclick="ouvrirCustomModal('${p.id}')" class="w-full bg-gray-50 hover:bg-red-500 hover:text-white text-gray-800 font-bold py-2 px-4 rounded-xl transition flex items-center justify-center gap-2 border">
          <i class="fa-solid fa-plus text-xs"></i> Sélectionner
        </button>
      </div>
    </div>`;
  });
}

function filtrerMenu(cat) {
  document.querySelectorAll(".menu-tab").forEach(btn => {
    btn.classList.remove("active", "bg-red-500", "text-white");
    btn.classList.add("bg-white");
  });
  
  const activeBtnId = cat === 'tous' ? 'tab-tous' : `tab-${cat.replace(/ /g, '-')}`;
  const activeBtn = document.getElementById(activeBtnId);
  
  if (activeBtn) {
    activeBtn.classList.add("active", "bg-red-500", "text-white");
    activeBtn.classList.remove("bg-white");
  }
  
  if (cat === "tous") {
    afficherMenu(PLATS);
  } else {
    const platsFiltres = PLATS.filter(p => p.categorie === cat);
    afficherMenu(platsFiltres);
  }
}

function ouvrirCustomModal(id_plat) {
  const plat = PLATS.find(p => p.id == id_plat);
  if (!plat) return console.error("Plat introuvable");
  document.getElementById("modal-item-id").value = plat.id;
  document.getElementById("modal-item-title").innerText = plat.nom || plat.titre;
  document.getElementById("modal-item-price").innerText = `${parseFloat(plat.prix).toFixed(2)} FCFA`;
  document.getElementById("custom-form").reset();
  document.getElementById("custom-modal").classList.remove("hidden");
}

function closeCustomModal() { document.getElementById("custom-modal").classList.add("hidden"); }

function validerAjoutPanier(e) {
  e.preventDefault();
  const plat = PLATS.find(p => p.id == document.getElementById("modal-item-id").value);
  const notes = document.getElementById("opt-notes").value;
  let specs = [];
  if (notes) specs.push(`Note : "${notes}"`);
  panier.push({ id: plat.id, titre: plat.nom || plat.titre, prix: parseFloat(plat.prix), quantite: 1, personnalisation: specs.join(" | ") || "Standard" });
  sauvegarderPanier(); // Sauvegarder après ajout
  mettreAJourPanierVisuel();
  closeCustomModal();
}

function mettreAJourPanierVisuel() {
  document.getElementById("panier-count").innerText = panier.length;
  const container = document.getElementById("panier-items");
  if (panier.length === 0) { 
    container.innerHTML = '<p class="text-gray-500 text-center py-8">Votre panier est vide.</p>'; 
    document.getElementById("panier-total").innerText = "0.00 FCFA"; 
    return; 
  }
  container.innerHTML = "";
  let total = 0;
  panier.forEach((item, index) => {
    total += item.prix;
    container.innerHTML += `<div class="flex justify-between items-start bg-gray-50 p-3 rounded-xl border"><div><p class="font-bold text-sm">${item.titre}</p><p class="text-xs text-gray-500 mt-0.5">${item.personnalisation}</p></div><div class="flex items-center gap-4"><span class="font-bold text-sm text-red-500">${item.prix.toFixed(2)} FCFA</span><button onclick="retirerDuPanier(${index})" class="text-gray-400 hover:text-red-500"><i class="fa-solid fa-trash-can"></i></button></div></div>`;
  });
  document.getElementById("panier-total").innerText = `${total.toFixed(2)} FCFA`;
}

function retirerDuPanier(index) { 
  panier.splice(index, 1); 
  sauvegarderPanier(); // Sauvegarder après suppression
  mettreAJourPanierVisuel(); 
}

function showToast(type, title, message, duration = 4000) {
  document.querySelectorAll('.toast-notification').forEach(toast => toast.remove());
  const toast = document.createElement('div');
  toast.className = `toast-notification toast-${type}`;
  const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
  toast.innerHTML = `<div class="${type === 'success' ? 'toast-success' : 'toast-error'}" style="position: relative; overflow: hidden;"><i class="fa-solid ${icon}"></i><div class="toast-content"><div class="toast-title">${title}</div><div class="toast-message">${message}</div></div><button onclick="this.closest('.toast-notification').remove()" style="background: none; border: none; color: white; cursor: pointer; font-size: 18px;"><i class="fa-solid fa-xmark"></i></button><div class="toast-progress"></div></div>`;
  document.body.appendChild(toast);
  setTimeout(() => { if (toast && toast.parentNode) { toast.classList.add('toast-hide'); setTimeout(() => toast.remove(), 300); } }, duration);
}

function passerCommande(e) {
  if (e && e.preventDefault) e.preventDefault();
  if (panier.length === 0) return showToast('error', 'Panier vide', 'Ajoutez des plats à votre panier.', 3000);
  
  const nomClient = document.getElementById("client-name").value.trim();
  const telephoneClient = document.getElementById("client-phone").value.trim();
  const emailClient = document.getElementById("client-email").value.trim();
  const typeCommande = document.getElementById("order-type").value;
  const idTable = document.getElementById("table-number").value;
  const adresseLivraison = document.getElementById("delivery-address").value.trim();
  
  if (!nomClient) return showToast('error', 'Nom manquant', 'Veuillez renseigner votre nom.', 3000);
  if (!emailClient) return showToast('error', 'Email manquant', 'Veuillez renseigner votre email.', 3000);
  
  let totalPanier = panier.reduce((sum, item) => sum + item.prix, 0);
  const commandeData = { 
    type: typeCommande, 
    id_table: idTable ? parseInt(idTable) : null, 
    nom_client: nomClient, 
    telephone_client: telephoneClient, 
    email: emailClient, 
    adresse_livraison: adresseLivraison || null, 
    montant_total: totalPanier, 
    panier: panier 
  };
  
  const btnSubmit = document.querySelector("#form-expedition button[type='submit']");
  const texteDorigine = btnSubmit?.innerText || "Valider";
  if (btnSubmit) { btnSubmit.innerText = "Traitement..."; btnSubmit.disabled = true; }
  
  fetch("../api/creer_commande.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(commandeData) })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Sauvegarder l'ID de la commande active
        localStorage.setItem('restobenin_commande_active', data.id_commande);
        // Vider le panier
        panier = [];
        sauvegarderPanier();
        mettreAJourPanierVisuel();
        
        if (typeof ouvrirPopupCommande === "function") ouvrirPopupCommande();
        
        document.getElementById("suivi-id").innerText = `#${data.id_commande}`;
        document.getElementById("suivi-vide").classList.add("hidden");
        document.getElementById("suivi-actif").classList.remove("hidden");
        
        if (typeof toggleShippingModal === "function") toggleShippingModal();
        
        document.getElementById("suivi").scrollIntoView({ behavior: "smooth" });
        
        // Démarrer le suivi en temps réel
        demarrerSuiviTempsReel(data.id_commande);
      } else showToast('error', 'Erreur', data.message || 'Erreur serveur', 4000);
    })
    .catch(error => showToast('error', 'Échec', 'Problème de connexion.', 4000))
    .finally(() => { if (btnSubmit) { btnSubmit.innerText = texteDorigine; btnSubmit.disabled = false; } });
}

// Suivi en temps réel (polling)
let intervalSuivi = null;

function demarrerSuiviTempsReel(idCommande) {
  if (intervalSuivi) clearInterval(intervalSuivi);
  
  function verifierStatut() {
    fetch(`../api/get_commande.php?id_commande=${idCommande}`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.commande) {
          mettreAJourSuivi(data.commande.statut);
          // Si commande terminée, arrêter le polling
          if (data.commande.statut === 'prete' || data.commande.statut === 'livree') {
            if (intervalSuivi) clearInterval(intervalSuivi);
            intervalSuivi = null;
          }
        }
      })
      .catch(error => console.error("Erreur suivi:", error));
  }
  
  verifierStatut(); // Premier appel immédiat
  intervalSuivi = setInterval(verifierStatut, 5000); // Puis toutes les 5 secondes
}

function traiterReservation(e) {
  e.preventDefault();
  const nom = document.getElementById("res-nom").value.trim();
  const telephone = document.getElementById("res-telephone").value.trim();
  const email = document.getElementById("res-email").value.trim();
  const nbPersonnes = document.getElementById("res-nb-personnes").value;
  const date = document.getElementById("res-date").value;
  const heure = document.getElementById("res-heure").value;
  const commentaire = document.getElementById("res-commentaire").value;
  
  if (!nom || !telephone || !email || !date || !heure) return showToast('error', 'Champs manquants', 'Tous les champs sont requis.', 3000);
  
  const btn = document.getElementById("res-btn");
  const texteOriginal = btn.innerText;
  btn.innerText = "Traitement...";
  btn.disabled = true;
  
  fetch("../api/creer_reservation.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ nom_client: nom, telephone: telephone, email: email, nb_personnes: parseInt(nbPersonnes), date: date, heure: heure, commentaire: commentaire }) })
    .then(response => response.json())
    .then(data => { 
      if (data.success) { 
        if (typeof ouvrirPopupReservation === "function") ouvrirPopupReservation();
        document.getElementById("reservation-form").reset(); 
        initReservationForm(); 
      } else showToast('error', 'Erreur', data.message, 4000); 
    })
    .catch(error => showToast('error', 'Erreur réseau', 'Impossible de contacter le serveur.', 4000))
    .finally(() => { btn.innerText = texteOriginal; btn.disabled = false; });
}

function initReservationForm() {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  const dateInput = document.getElementById("res-date");
  if (dateInput) dateInput.value = tomorrow.toISOString().split('T')[0];
  const form = document.getElementById("reservation-form");
  if (form) form.addEventListener("submit", traiterReservation);
}

function initialiserAvis() {
  fetch("../api/get_avis.php")
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById("reviews-container");
      if (!container) return;
      container.innerHTML = "";
      if (Array.isArray(data) && data.length > 0) data.forEach(avis => insertionVisuelleAvis(avis.nom_client, avis.commentaire, avis.note));
      else AVIS_INITIALS.forEach(a => insertionVisuelleAvis(a.nom, a.texte, a.note));
    })
    .catch(error => { const container = document.getElementById("reviews-container"); if (container) { container.innerHTML = ""; AVIS_INITIALS.forEach(a => insertionVisuelleAvis(a.nom, a.texte, a.note)); } });
}

function setRating(note) {
  noteSelectionnee = note;
  const stars = document.getElementById("star-rating-container").children;
  for (let i = 0; i < 5; i++) stars[i].className = i < note ? "fa-solid fa-star cursor-pointer text-amber-400 transition" : "fa-solid fa-star cursor-pointer text-gray-300 transition";
}

function ajouterAvis(e) {
  e.preventDefault();
  const nom = document.getElementById("avis-nom").value.trim();
  const texte = document.getElementById("avis-texte").value.trim();
  
  if (!nom || !texte) return showToast('error', 'Champs manquants', 'Veuillez remplir votre nom et votre avis.', 3000);
  
  const btnSubmit = e.target.querySelector("button[type='submit']");
  const texteOriginal = btnSubmit.innerText;
  btnSubmit.innerText = "Envoi...";
  btnSubmit.disabled = true;
  
  fetch("../api/creer_avis.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ nom_client: nom, note: noteSelectionnee, commentaire: texte }) })
    .then(response => response.json())
    .then(data => { 
      if (data.success) { 
        insertionVisuelleAvis(`${nom} (Vous)`, texte, noteSelectionnee, true);
        if (typeof ouvrirPopupAvis === "function") ouvrirPopupAvis();
        e.target.reset(); 
        setRating(5); 
      } else showToast('error', 'Erreur', data.message, 4000); 
    })
    .catch(error => { 
      insertionVisuelleAvis(`${nom} (Vous)`, texte, noteSelectionnee, true);
      e.target.reset(); 
      setRating(5);
      if (typeof ouvrirPopupAvis === "function") ouvrirPopupAvis();
    })
    .finally(() => { btnSubmit.innerText = texteOriginal; btnSubmit.disabled = false; });
}

function insertionVisuelleAvis(nom, texte, note, estNouveau = false) {
  const container = document.getElementById("reviews-container");
  if (!container) return;
  let etoilesHtml = "";
  for (let i = 0; i < 5; i++) etoilesHtml += i < note ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star text-gray-300"></i>';
  const classeBonus = estNouveau ? "border-red-100 bg-red-50/10" : "";
  container.innerHTML = `<div class="bg-white p-6 rounded-2xl shadow-sm border ${classeBonus}"><div class="flex justify-between items-center mb-2"><span class="font-bold">${nom}</span><div class="text-amber-400 text-sm">${etoilesHtml}</div></div><p class="text-gray-600 text-sm">${texte}</p></div>` + container.innerHTML;
}