<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { ref } from 'vue'
import { QrcodeStream } from 'vue-qrcode-reader'
import {
    ShieldCheckIcon,
    MagnifyingGlassIcon,
    ExclamationTriangleIcon,
    DocumentTextIcon,
    HeartIcon,
    UserGroupIcon,
    CheckCircleIcon,
    ArrowRightIcon,
    QrCodeIcon,
    LockClosedIcon,
    BuildingOffice2Icon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const form = useForm({
    reference: '',
})

const isFocused = ref(false)


const isScanning = ref(false)
const scanError = ref('')

const onDetect = (detectedCodes) => {
    if (detectedCodes && detectedCodes.length > 0) {
        const result = detectedCodes[0].rawValue;
        isScanning.value = false;
        
        if (result.startsWith('http://') || result.startsWith('https://')) {
            window.location.href = result;
        } else {
            form.reference = result;
            submit();
        }
    }
}

const onInit = async (promise) => {
    try {
        await promise;
        scanError.value = '';
    } catch (error) {
        if (error.name === 'NotAllowedError') {
            scanError.value = "Erreur: Autorisation de l'appareil photo refusée.";
        } else if (error.name === 'NotFoundError') {
            scanError.value = "Erreur: Aucun appareil photo trouvé.";
        } else if (error.name === 'NotSupportedError') {
            scanError.value = "Erreur: Connexion non sécurisée (HTTPS requis).";
        } else if (error.name === 'NotReadableError') {
            scanError.value = "Erreur: L'appareil photo est peut-être déjà utilisé.";
        } else if (error.name === 'OverconstrainedError') {
            scanError.value = "Erreur: Caméra non compatible.";
        } else if (error.name === 'StreamApiNotSupportedError') {
            scanError.value = "Erreur: Navigateur non supporté.";
        } else {
            scanError.value = `Erreur de caméra (${error.name})`;
        }
    }
}

const submit = () => {
    form.post(route('certificates.search'))
}

const actTypes = [
    {
        icon: DocumentTextIcon,
        label: 'Actes de Naissance',
        color: 'from-emerald-500 to-green-600',
        bg: 'bg-emerald-50',
        text: 'text-emerald-700',
        border: 'border-emerald-200',
    },
    {
        icon: HeartIcon,
        label: 'Actes de Mariage',
        color: 'from-rose-400 to-pink-600',
        bg: 'bg-rose-50',
        text: 'text-rose-700',
        border: 'border-rose-200',
    },
    {
        icon: UserGroupIcon,
        label: 'Actes de Décès',
        color: 'from-slate-500 to-gray-700',
        bg: 'bg-slate-50',
        text: 'text-slate-700',
        border: 'border-slate-200',
    },
    {
        icon: BuildingOffice2Icon,
    XMarkIcon,
        label: 'Certificats Civils',
        color: 'from-blue-500 to-indigo-600',
        bg: 'bg-blue-50',
        text: 'text-blue-700',
        border: 'border-blue-200',
    },
]

const steps = [
    { num: '01', title: 'Saisir', desc: 'Entrez le numéro de référence figurant sur votre document officiel.' },
    { num: '02', title: 'Vérifier', desc: 'Le système consulte le registre numérique sécurisé en temps réel.' },
    { num: '03', title: 'Consulter', desc: 'Les informations officielles certifiées vous sont présentées.' },
]

const trusts = [
    { icon: QrCodeIcon, title: 'QR Code Sécurisé', desc: 'Vérification instantanée via le QR code imprimé sur le document.' },
    { icon: LockClosedIcon, title: 'Chiffrement SSL', desc: 'Toutes les données transitent de manière chiffrée et sécurisée.' },
    { icon: ShieldCheckIcon, title: 'Registre Officiel', desc: 'Données issues du registre central de l\'État Civil de Enampore.' },
]
</script>

<template>
    <Head title="Portail de Vérification — État Civil de Enampore" />

    <GuestLayout>
        <!-- ═══════════════════════════════════════════════════
             HERO SECTION
        ════════════════════════════════════════════════════ -->
        <section class="relative overflow-hidden">
            <!-- Background vert institutionnel -->
            <div class="absolute inset-0 bg-gradient-to-br from-[#0d4a08] via-[#1E690F] to-[#2d8a1f]"></div>
            <!-- Motif géométrique discret -->
            <div class="absolute inset-0 opacity-5"
                 style="background-image: repeating-linear-gradient(45deg, white 0, white 1px, transparent 0, transparent 50%); background-size: 30px 30px;"></div>
            <!-- Cercles décoratifs -->
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -left-10 w-60 h-60 bg-[#F0C31E]/10 rounded-full blur-2xl"></div>

            <div class="relative max-w-5xl mx-auto px-4 py-20 sm:py-28 text-center">
                <!-- Badge République -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-white/80 text-[10px] font-black uppercase tracking-[0.3em] mb-8">
                    <span class="w-2 h-2 rounded-full bg-[#F0C31E] animate-pulse"></span>
                    République du Sénégal — Commune de Enampore
                </div>

                <!-- Icône principale -->
                <div class="flex justify-center mb-8">
                    <div class="relative">
                        <div class="w-24 h-24 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center shadow-2xl">
                            <ShieldCheckIcon class="h-14 w-14 text-white" />
                        </div>
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-[#F0C31E] rounded-full flex items-center justify-center">
                            <CheckCircleIcon class="h-4 w-4 text-[#0d4a08]" />
                        </div>
                    </div>
                </div>

                <!-- Titre -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-none mb-5">
                    Portail de Vérification<br />
                    <span class="text-[#F0C31E]">Officiel</span>
                </h1>
                <p class="text-white/70 text-lg font-medium max-w-2xl mx-auto leading-relaxed mb-12">
                    Vérifiez en ligne l'authenticité d'un acte d'état civil ou d'un certificat officiel
                    émis par la Mairie de Enampore.
                </p>

                <!-- ══ Formulaire de recherche ══ -->
                <div class="max-w-2xl mx-auto">
                    <!-- Erreur flash -->
                    <div v-if="$page.props.flash.error"
                         class="mb-5 p-4 bg-red-500/20 border border-red-400/40 backdrop-blur-md rounded-2xl flex items-center gap-3 text-white">
                        <ExclamationTriangleIcon class="h-5 w-5 text-red-300 shrink-0" />
                        <p class="text-sm font-bold">{{ $page.props.flash.error }}</p>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="relative group">
                            <!-- Glow effect au focus -->
                            <div class="absolute -inset-0.5 rounded-2xl transition-all duration-300"
                                 :class="isFocused ? 'bg-gradient-to-r from-[#F0C31E] to-yellow-300 opacity-60 blur-sm' : 'opacity-0'">
                            </div>

                            <div class="relative flex flex-col sm:flex-row bg-white rounded-2xl shadow-2xl overflow-hidden">
                                <div class="flex flex-1 items-center">
                                    <div class="flex items-center pl-4 sm:pl-6 text-gray-400">
                                        <MagnifyingGlassIcon class="h-5 w-5 sm:h-6 sm:w-6" :class="isFocused ? 'text-[#1E690F]' : ''" style="transition: color 0.2s" />
                                    </div>
                                    <input
                                        v-model="form.reference"
                                        type="text"
                                        placeholder="Ex : ENAM-N-2026-0001"
                                        class="flex-1 min-w-0 px-3 py-4 sm:px-5 sm:py-5 text-gray-900 font-bold text-base sm:text-lg bg-transparent border-0 focus:ring-0 placeholder:font-normal placeholder:text-gray-400 uppercase"
                                        style="outline: none;"
                                        @focus="isFocused = true"
                                        @blur="isFocused = false"
                                        required
                                    />
                                    <button type="button" @click="isScanning = true" class="flex items-center justify-center px-4 sm:px-6 py-4 sm:py-5 text-gray-400 hover:text-[#1E690F] transition-colors border-l border-gray-100 bg-gray-50/50" title="Scanner le QR Code">
                                        <QrCodeIcon class="h-6 w-6 sm:h-7 sm:w-7" />
                                    </button>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-4 sm:px-8 sm:py-5 font-black text-[#0d4a08] text-sm transition-all duration-200 active:scale-95 disabled:opacity-60 shrink-0"
                                    style="background: linear-gradient(135deg, #F0C31E 0%, #e6b800 100%);"
                                >
                                    <span v-if="form.processing">...</span>
                                    <span v-else>Vérifier</span>
                                    <ArrowRightIcon v-if="!form.processing" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <p class="text-white/40 text-xs font-medium mt-3">
                            Le numéro de référence figure sur votre document officiel (ex. : ENAM-N-2026-0001)
                        </p>
                    </form>
                </div>

                <!-- Types d'actes couverts -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl mx-auto mt-12">
                    <div
                        v-for="act in actTypes"
                        :key="act.label"
                        class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-4 text-center hover:bg-white/15 transition-all duration-200"
                    >
                        <div class="w-10 h-10 rounded-xl mx-auto mb-2 flex items-center justify-center"
                             style="background: rgba(255,255,255,0.15)">
                            <component :is="act.icon" class="h-5 w-5 text-white" />
                        </div>
                        <p class="text-white/80 text-xs font-bold leading-tight">{{ act.label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════
             COMMENT ÇA MARCHE
        ════════════════════════════════════════════════════ -->
        <section class="py-20 bg-white">
            <div class="max-w-5xl mx-auto px-4">
                <div class="text-center mb-14">
                    <p class="text-[10px] font-black text-[#1E690F] uppercase tracking-[0.4em] mb-3">Processus</p>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Comment ça marche ?</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="(step, i) in steps" :key="step.num" class="relative">
                        <!-- Connecteur -->
                        <div v-if="i < steps.length - 1"
                             class="hidden md:block absolute top-8 left-[calc(100%-0px)] w-full h-px bg-gradient-to-r from-[#1E690F]/20 to-transparent z-0"></div>

                        <div class="relative z-10 text-center p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 bg-white">
                            <!-- Numéro -->
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl font-black text-2xl text-white mb-5 shadow-lg"
                                 style="background: linear-gradient(135deg, #1E690F 0%, #3D9426 100%);">
                                {{ step.num }}
                            </div>
                            <h3 class="text-xl font-black text-gray-900 mb-3">{{ step.title }}</h3>
                            <p class="text-sm text-gray-500 font-medium leading-relaxed">{{ step.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════
             GARANTIES DE SÉCURITÉ
        ════════════════════════════════════════════════════ -->
        <section class="py-20" style="background: linear-gradient(180deg, #F2F9EE 0%, #E8F5E1 100%);">
            <div class="max-w-5xl mx-auto px-4">
                <div class="text-center mb-14">
                    <p class="text-[10px] font-black text-[#1E690F] uppercase tracking-[0.4em] mb-3">Sécurité</p>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Un service de confiance</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="trust in trusts"
                        :key="trust.title"
                        class="bg-white rounded-3xl p-8 shadow-sm border border-green-100 hover:shadow-md transition-all duration-300"
                    >
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-5 shadow-sm"
                             style="background: linear-gradient(135deg, #1E690F 0%, #3D9426 100%);">
                            <component :is="trust.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="font-black text-gray-900 mb-2">{{ trust.title }}</h3>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed">{{ trust.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Scanner Modal -->
        <Teleport to="body">
            <div v-if="isScanning" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm">
                <div class="bg-white rounded-3xl overflow-hidden w-full max-w-md shadow-2xl relative">
                    <div class="p-4 border-b flex justify-between items-center bg-gray-50">
                        <h3 class="font-black text-gray-900 flex items-center gap-2 text-sm uppercase tracking-widest">
                            <QrCodeIcon class="h-5 w-5 text-[#1E690F]" />
                            Scanner le document
                        </h3>
                        <button @click="isScanning = false" class="p-2 text-gray-500 hover:text-red-500 rounded-full hover:bg-gray-200 transition-colors">
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <div v-if="scanError" class="mb-4 p-3 bg-red-50 text-red-700 rounded-xl text-sm font-bold flex items-center gap-2">
                            <ExclamationTriangleIcon class="h-5 w-5" />
                            {{ scanError }}
                        </div>
                        
                        <div class="aspect-square bg-black rounded-2xl overflow-hidden relative shadow-inner">
                            <qrcode-stream 
                                v-if="isScanning" 
                                @detect="onDetect" 
                                @camera-on="onInit"
                                class="w-full h-full object-cover"
                            ></qrcode-stream>
                            
                            <!-- Scanner Target Overlay -->
                            <div class="absolute inset-0 pointer-events-none border-[2px] border-[#F0C31E]/50 rounded-2xl m-8 sm:m-12 transition-all duration-1000 animate-pulse">
                                <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-[#F0C31E] -mt-[2px] -ml-[2px] rounded-tl-xl"></div>
                                <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-[#F0C31E] -mt-[2px] -mr-[2px] rounded-tr-xl"></div>
                                <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-[#F0C31E] -mb-[2px] -ml-[2px] rounded-bl-xl"></div>
                                <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-[#F0C31E] -mb-[2px] -mr-[2px] rounded-br-xl"></div>
                            </div>
                        </div>
                        <p class="text-center text-[10px] font-black text-gray-400 mt-4 uppercase tracking-widest">
                            Pointez votre caméra vers le QR Code figurant sur l'acte ou le certificat
                        </p>
                    </div>
                </div>
            </div>
        </Teleport>
    </GuestLayout>
</template>
