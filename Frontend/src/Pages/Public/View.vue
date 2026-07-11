<script setup>
import { Head, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import {
    CheckBadgeIcon, ArrowLeftIcon, DocumentTextIcon,
    CalendarIcon, MapPinIcon, UserIcon, HashtagIcon,
    ShieldCheckIcon, ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    certificate: Object,
})

const formatType = (type) => {
    const dictionary = {
        residence:              'Certificat de résidence',
        coutume:               'Certificat de coutume',
        indigence:             "Certificat d'indigence",
        individualite:         "Certificat d'individualité",
        vie_collective:        'Certificat de vie collective',
        vie_individuel:        'Certificat de vie individuelle',
        non_inscrit_naissance: 'Certificat de non inscrit de naissance',
        acte_non_inexistant:   "Certificat d'acte non inexistant",
    }
    return dictionary[type] || type
}
</script>

<template>
    <Head title="Certificat Validé — Vérification Officielle" />
    <GuestLayout>
        <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

            <!-- Retour -->
            <Link :href="route('certificates.verify')"
                  class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-[#1E690F] transition-colors group mb-8">
                <ArrowLeftIcon class="h-4 w-4 group-hover:-translate-x-1 transition-transform" />
                Nouvelle vérification
            </Link>

            <div class="bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 overflow-hidden">

                <!-- Header -->
                <div class="bg-gradient-to-br from-[#1E690F] to-[#3D9426] px-8 py-12 flex flex-col items-center text-center text-white relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10"
                         style="background-image: repeating-linear-gradient(45deg,white 0,white 1px,transparent 0,transparent 50%); background-size:24px 24px;"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-[0.2em] mb-6">
                            <CheckBadgeIcon class="h-4 w-4" />
                            Document Authentique et Validé
                        </div>
                        <h1 class="text-3xl font-black tracking-tight mb-2">{{ formatType(certificate.type) }}</h1>
                        <p class="text-white/70 font-bold tracking-widest uppercase text-xs">Réf : {{ certificate.reference_number }}</p>
                    </div>
                </div>

                <div class="p-8 md:p-12 space-y-10">

                    <!-- Identité & Validation -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <!-- Identité -->
                        <section class="space-y-5">
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3">Identité du sujet</h2>

                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl"><UserIcon class="h-5 w-5 text-[#1E690F]" /></div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Prénom &amp; Nom</p>
                                    <p class="text-lg font-black text-gray-900 leading-tight uppercase">{{ certificate.applicant_first_name }} {{ certificate.applicant_last_name }}</p>
                                </div>
                            </div>

                            <div v-if="certificate.applicant_cni" class="flex items-start gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl"><HashtagIcon class="h-5 w-5 text-[#1E690F]" /></div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Numéro CNI</p>
                                    <p class="text-sm font-black text-gray-900 font-mono">{{ certificate.applicant_cni }}</p>
                                </div>
                            </div>

                            <div v-if="certificate.center" class="flex items-start gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl"><MapPinIcon class="h-5 w-5 text-[#1E690F]" /></div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Centre d'État Civil</p>
                                    <p class="text-sm font-bold text-gray-700">{{ certificate.center }}</p>
                                </div>
                            </div>
                        </section>

                        <!-- Validation -->
                        <section class="space-y-5">
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3">Validation Registre</h2>

                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-green-50 rounded-xl"><CheckBadgeIcon class="h-5 w-5 text-green-600" /></div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Statut Final</p>
                                    <p class="text-sm font-black text-green-700 uppercase">Clôturé &amp; Délivré</p>
                                </div>
                            </div>

                            <div v-if="certificate.validated_at" class="flex items-start gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl"><CalendarIcon class="h-5 w-5 text-[#1E690F]" /></div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter mb-1">Date d'émission</p>
                                    <p class="text-sm font-bold text-gray-700">
                                        {{ new Date(certificate.validated_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="certificate.is_signed" class="p-4 bg-[#F2F9EE] border border-[#B3D9A0] rounded-2xl flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-white shrink-0" style="background: linear-gradient(135deg, #1E690F, #3D9426)">
                                    <ShieldCheckIcon class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-[#1E690F] uppercase tracking-widest leading-none">Certificat Signé</p>
                                    <p class="text-[10px] text-[#1E690F] font-bold mt-1">Signature Électronique Vérifiée</p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Données spécifiques -->
                    <section v-if="certificate.data && Object.keys(certificate.data).length">
                        <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Attestation de faits</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="(value, key) in certificate.data" :key="key" class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ String(key).replace(/_/g, ' ') }}</p>
                                <p class="text-sm font-bold text-gray-700 leading-relaxed whitespace-pre-wrap">{{ value || 'N/A' }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- Note -->
                    <div class="p-6 bg-amber-50 rounded-2xl border border-amber-100 flex items-start gap-4">
                        <ExclamationTriangleIcon class="h-6 w-6 text-amber-600 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-sm font-bold text-amber-900 mb-1">Note de conformité</p>
                            <p class="text-xs text-amber-800 leading-relaxed">Ce document numérique fait foi dans la limite des informations enregistrées au registre national. Toute altération physique ou numérique rend ce certificat caduc.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-8 py-5 text-center border-t border-gray-100">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.5em]">SIG-EC — Mairie de Enampore — République du Sénégal</p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
