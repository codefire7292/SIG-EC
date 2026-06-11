#!/bin/bash

# =========================================================================
# SCRIPT DE LIAISON DNS & CONFIGURATION CLOUDFLARE TUNNEL (SIG-EC)
# =========================================================================

# Couleurs pour l'affichage
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

# =========================================================================
# CONFIGURATION DES PARAMÈTRES (À MODIFIER AVEC VOS INFOS CLOUDFLARE)
# =========================================================================
# 1. Collez ici le jeton unique fourni par Cloudflare Zero Trust lors de la création du tunnel
CLOUDFLARE_TOKEN="eyJhIjoiMGJjNGU4Y2Q0ZjI2ZTc0ZTllOThmNjcyNTVmM2Q2NDUiLCJ0IjoiMDFiNTcwN2UtY2MxNy00YmZjLThmN2MtYjIxNThhNjU4ODdkIiwicyI6Ik1qZzJNelkxT1RBdFpXSmlNQzAwT1RNMkxUa3lPV0l0TUdVNVpESTNNekUwTURVNCJ9"

# 2. Renseignez votre nom de domaine ou sous-domaine exact
DOMAINE_APPLICATION="c-enampore.sn"
# =========================================================================

echo -e "${BLUE}=== Début de la configuration Cloudflare Tunnel ===${NC}"

# Vérification de sécurité si les valeurs par défaut n'ont pas été changées
if [ "$CLOUDFLARE_TOKEN" == "VOTRE_TOKEN_ICI" ] || [ "$DOMAINE_APPLICATION" == "sigec.votre-domaine.sn" ]; then
    echo -e "${RED}✘ Erreur : Veuillez éditer le script pour y insérer votre vrai Token et Domaine fourni par Cloudflare.${NC}"
    exit 1
fi

# -------------------------------------------------------------------------
# ÉTAPE 1 : Téléchargement et Installation du connecteur Cloudflared
# -------------------------------------------------------------------------
echo -e "\n${BLUE}[1/3] Téléchargement du paquet officiel cloudflared...${NC}"
curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✔ Paquet cloudflared téléchargé avec succès.${NC}"
else
    echo -e "${RED}✘ Erreur lors du téléchargement du paquet.${NC}"
    exit 1
fi

echo -e "\n${BLUE}[2/3] Installation du paquet sur le système Ubuntu...${NC}"
sudo dpkg -i cloudflared.deb

# Suppression du fichier d'installation temporaire
rm cloudflared.deb

# -------------------------------------------------------------------------
# ÉTAPE 2 : Démarrage du service et connexion réseau
# -------------------------------------------------------------------------
echo -e "\n${BLUE}[3/3] Liaison sécurisée du serveur avec votre tunnel Cloudflare...${NC}"
sudo cloudflared service install "$CLOUDFLARE_TOKEN"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✔ Le service arrière-plan cloudflared est actif et configuré !${NC}"
else
    echo -e "${RED}✘ Échec de l'installation du service tunnel.${NC}"
    exit 1
fi

# -------------------------------------------------------------------------
# ÉTAPE 3 : Rappel des liaisons locales
# -------------------------------------------------------------------------
echo -e "\n${BLUE}=== Configuration finale requise ===${NC}"
echo -e "1. Côté serveur : Votre bloc Nginx doit écouter sur le port 80 avec :"
echo -e "   ${BLUE}server_name ${DOMAINE_APPLICATION};${NC}"
echo -e "2. Côté Cloudflare Dashboard : Dans l'onglet 'Public Hostname' du tunnel,"
echo -e "   liez ${BLUE}${DOMAINE_APPLICATION}${NC} vers ${GREEN}http://localhost:80${NC}"

echo -e "\n${GREEN}=========================================================================${NC}"
echo -e "${GREEN} CONFIGURATION EFFECTUÉE : Le script de liaison est prêt à l'emploi. ${NC}"
echo -e "${GREEN}=========================================================================${NC}"