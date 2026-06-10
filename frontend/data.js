// Base de données globale de vos plats (Remplie dynamiquement via l'API MySQL)
let PLATS = [];

// Avis statiques par défaut à l'initialisation
const AVIS_INITIALS = [
    { nom: "Marc L.", texte: "Le burger Yummy est incroyable ! La viande est juteuse.", note: 5 },
    { nom: "Sophie T.", texte: "Superbe accueil, options de personnalisation très claires.", note: 4 }
];