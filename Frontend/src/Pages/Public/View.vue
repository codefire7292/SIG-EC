<script setup>
import { Head, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { 
    CheckBadgeIcon, 
    ArrowLeftIcon,
    DocumentTextIcon,
    CalendarIcon,
    MapPinIcon,
    UserIcon,
    HashtagIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    certificate: Object,
})

const formatType = (type) => {
    const dictionary = {
        residence: 'Certificat de résidence',
        coutume: 'Certificat de coutume',
        indigence: 'Certificat d\'indigence',
        individualite: 'Certificat d\'individualité',
        vie_collective: 'Certificat de vie collective',
        vie_individuel: 'Certificat de vie individuelle',
        non_inscrit_naissance: 'Certificat de non inscrit de naissance',
        acte_non_inexistant: 'Certificat d\'acte non inexistant',
    }
    return dictionary[type] || type
}
</script>

<template>
    <Head title="Vue Certificat Validé" />

    <GuestLayout>
        <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <Link :href="route('certificates.verify')" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-blue-600 transition-colors group">
                    <ArrowLeftIcon class="h-4 w-4 group-hover:-translate-x-1 transition-transform" />
                    Nouvelle vérification
                </Link>
            </div>

            <div class="bg-white rounded-[3rem] shadow-2xl shadow-blue-100 border border-gray-100 overflow-hidden">
                <!-- Header Badge -->
                <div class="bg-blue-600 px-8 py-10 flex flex-col items-center text-center text-white relative">
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <ShieldCheckIcon class="h-32 w-32" />
                    </div>
                    
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-[0.2em] mb-6">
                        <CheckBadgeIcon class="h-4 w-4" />
                        Document Authentique et Validé
                    </div>
                    
                    <h1 class="text-3xl font-black tracking-tighter mb-2">{{ formatType(certificate.type) }}</h1>
                    <p class="text-blue-100 font-bold tracking-widest uppercase text-xs">Réf: {{ certificate.reference_number }}</p>
                </div>

                <div class="p-8 md:p-12">
                    <!-- Main Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <!-- Identité -->
                        <div class="space-y-6">
                            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3">Identité du sujet</h3>
                            
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <UserIcon class="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Prénom & Nom</p>
                                    <p class="text-lg font-black text-gray-900 leading-tight uppercase">{{ certificate.applicant_first_name }} {{ certificate.applicant_last_name }}</p>
                                </div>
                            </div>

                            <div v-if="certificate.applicant_cni" class="flex items-start gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <HashtagIcon class="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Numéro CNI</p>
                                    <p class="text-sm font-black text-gray-900 font-mono">{{ certificate.applicant_cni }}</p>
                                </div>
                            </div>

                            <div v-if="certificate.center" class="flex items-start gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <MapPinIcon class="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Centre d'État Civil</p>
                                    <p class="text-sm font-bold text-gray-700">{{ certificate.center }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Statut & Validation -->
                        <div class="space-y-6">
                            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3">Validation Registre</h3>
                            
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-green-50 rounded-xl">
                                    <CheckBadgeIcon class="h-5 w-5 text-green-600" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Statut Final</p>
                                    <p class="text-sm font-black text-green-700 uppercase">Clôturé & Délivré</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <CalendarIcon class="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Date d'émission</p>
                                    <p class="text-sm font-bold text-gray-700">{{ new Date(certificate.validated_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                                </div>
                            </div>

                            <div v-if="certificate.is_signed" class="p-4 bg-blue-50 border border-blue-100 rounded-2xl flex items-center gap-3">
                                <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                                    <ShieldCheckIcon class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest leading-none">Certificat Signé</p>
                                    <p class="text-[10px] text-blue-800 font-bold mt-1">Signature Électronique Vérifiée</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails Spécifiques -->
                    <div class="mt-12 pt-8 border-t border-gray-100">
                         <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Attestation de faits</h3>
                         <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                             <div v-for="(value, key) in certificate.data" :key="key" class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                 <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ key.replace(/_/g, ' ') }}</p>
                                 <p class="text-sm font-bold text-gray-700 leading-relaxed whitespace-pre-wrap">{{ value || 'N/A' }}</p>
                             </div>
                         </div>
                    </div>

                    <div class="mt-12 p-6 bg-yellow-50 rounded-[2rem] border border-yellow-100/50 flex items-start gap-4">
                        <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600 mt-1 shrink-0" />
                        <div>
                            <p class="text-sm font-bold text-yellow-900 mb-1">Note de conformité</p>
                            <p class="text-xs text-yellow-800 leading-relaxed">
                                Ce document numérique fait foi dans la limite des informations enregistrées au registre national. 
                                Toute altération physique ou numérique rend ce certificat caduc.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer Accent -->
                <div class="bg-gray-50 px-8 py-6 text-center border-t border-gray-100">
                     <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.5em]">SIG-EC — Plateforme de Certification Officielle</p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
