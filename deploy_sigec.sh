#!/bin/bash

# =========================================================================
# SCRIPT DE CONFIGURATION ENVIRONNEMENT ET DÉPLOIEMENT BACKEND (SIG-EC)
# =========================================================================

# Couleurs pour les messages de statut
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # Pas de couleur

echo -e "${BLUE}=== Début de la configuration du serveur ===${NC}"

# -------------------------------------------------------------------------
# ÉTAPE 1 : Nettoyage d'APT et installation de PHP & Extensions
# -------------------------------------------------------------------------
echo -e "\n${BLUE}[1/5] Nettoyage des dépôts et installation de PHP...${NC}"

# Retirer le PPA ondrej s'il est présent et pose problème (version resolute)
sudo add-apt-repository --remove ppa:ondrej/php -y

# Activer le dépôt universe (nécessaire pour certaines extensions)
sudo add-apt-repository universe -y

# Mise à jour de la liste des paquets
sudo apt update

# Installation de PHP (la version globale stable du système, ex: 8.5) et des extensions requises
sudo apt install -y php-fpm php-cli php-pgsql php-mbstring php-xml php-curl php-bcmath php-zip php-gd

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✔ PHP et ses extensions ont été installés avec succès !${NC}"
    php -v | head -n 1
else
    echo -e "${RED}✘ Erreur lors de l'installation de PHP.${NC}"
    exit 1
fi

# -------------------------------------------------------------------------
# ÉTAPE 2 : Vérification du dossier Backend
# -------------------------------------------------------------------------
echo -e "\n${BLUE}[2/5] Vérification de l'emplacement du projet...${NC}"

# Si on est à la racine de SIG-EC, on bascule automatiquement dans Backend
if [ -d "Backend" ]; then
    cd Backend
    echo -e "${GREEN}✔ Passage dans le dossier Backend réussi.${NC}"
elif [[ "$PWD" == *"/Backend" ]]; then
    echo -e "${GREEN}✔ Déjà positionné dans le dossier Backend.${NC}"
else
    echo -e "${RED}✘ Erreur : Fichier composer.json introuvable. Exécutez ce script depuis le dossier racine du projet ou depuis le dossier Backend.${NC}"
    exit 1
fi

# -------------------------------------------------------------------------
# ÉTAPE 3 : Nettoyage radical de l'environnement Composer (Anti-corruption)
# -------------------------------------------------------------------------
echo -e "\n${BLUE}[3/5] Nettoyage préventif des résidus corrompus...${NC}"

# Suppression du dossier vendor complet pour éviter les demi-extractions (ex: dompdf, thecodingmachine)
if [ -d "vendor" ]; then
    echo "Suppression de l'ancien dossier vendor..."
    rm -rf vendor
fi

# Vidage complet du cache local de Composer
echo "Vidage du cache de Composer..."
composer clear-cache

# Augmentation du timeout global de processus pour parer aux baisses de connexion internet
export COMPOSER_PROCESS_TIMEOUT=2000

# -------------------------------------------------------------------------
# ÉTAPE 4 : Installation propre des dépendances PHP via Composer
# -------------------------------------------------------------------------
echo -e "\n${BLUE}[4/5] Installation des dépendances via Composer...${NC}"

# Installation en mode production en ignorant les restrictions strictes de plateforme de PHP 8.5
composer install --ignore-platform-reqs --no-dev --optimize-autoloader

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✔ Composer a finalisé l'installation et généré l'autoloader avec succès !${NC}"
else
    echo -e "${RED}✘ Erreur lors de l'installation des dépendances Composer.${NC}"
    exit 1
fi

# -------------------------------------------------------------------------
# ÉTAPE 5 : Configuration du fichier d'environnement et Clé Laravel
# -------------------------------------------------------------------------
echo -e "\n${BLUE}[5/5] Configuration finale de Laravel...${NC}"

# Création du fichier .env s'il n'existe pas encore
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✔ Fichier .env créé à partir de .env.example.${NC}"
    else
        echo -e "${RED}⚠ Attention : Aucun fichier .env ou .env.example trouvé.${NC}"
    fi
else
    echo "Le fichier .env existe déjà, passage à la génération de la clé."
fi

# Génération de la clé de sécurité Laravel
if [ -f ".env" ]; then
    php artisan key:generate
    echo -e "${GREEN}✔ Clé Laravel générée.${NC}"
fi

echo -e "\n${GREEN}=========================================================================${NC}"
echo -e "${GREEN} TOUT EST OK ! Votre backend SIG-EC est prêt sur ce serveur. ${NC}"
echo -e "${GREEN}=========================================================================${NC}"