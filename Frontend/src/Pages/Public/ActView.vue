<script setup>
import { Head, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import {
    CheckBadgeIcon, ArrowLeftIcon, CalendarIcon, MapPinIcon,
    UserIcon, DocumentTextIcon, HeartIcon, UserGroupIcon,
    ShieldCheckIcon, ClockIcon, BuildingOffice2Icon, PaperClipIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    act: Object,
    type: String,
})

const typeConfig = {
    naissance: { label: 'Acte de Naissance', icon: DocumentTextIcon, color: 'from-emerald-500 to-green-700', light: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    mariage:   { label: 'Acte de Mariage',   icon: HeartIcon,         color: 'from-rose-400 to-pink-700',   light: 'bg-rose-50 text-rose-700 border-rose-200' },
    deces:     { label: 'Acte de Décès',     icon: UserGroupIcon,     color: 'from-slate-500 to-gray-800',  light: 'bg-slate-50 text-slate-700 border-slate-200' },
}

const config = typeConfig[props.type] ?? typeConfig.naissance

const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) : null
const formatGender = (g) => g === 'M' ? 'Masculin' : g === 'F' ? 'Féminin' : null
const formatOption = (o) => ({ monogamie: 'Monogamie', polygamie: 'Polygamie', polyandrie: 'Polyandrie' }[o] ?? o)
const formatRegime = (r) => ({ communaute_reduite: 'Communauté réduite aux acquêts', separation_biens: 'Séparation de biens' }[r] ?? r)

const docs = computed(() => {
    const a = props.act
    const map = {
        naissance: [
            { key: 'doc_cni_pere_path', label: 'CNI Père' },
            { key: 'doc_cni_mere_path', label: 'CNI Mère' },
            { key: 'doc_acte_naissance_path', label: 'Acte de naissance' },
            { key: 'doc_cni_declarant_path', label: 'CNI Déclarant' },
            { key: 'doc_jugement_path', label: 'Jugement' },
            { key: 'doc_autres_path', label: 'Autres pièces' },
        ],
        mariage: [
            { key: 'doc_cni_husband_path', label: 'CNI Époux' },
            { key: 'doc_cni_wife_path', label: 'CNI Épouse' },
            { key: 'doc_birth_husband_path', label: 'Naissance Époux' },
            { key: 'doc_birth_wife_path', label: 'Naissance Épouse' },
            { key: 'doc_medical_path', label: 'Certificat médical' },
            { key: 'doc_parental_auth_path', label: 'Autorisation parentale' },
            { key: 'doc_jugement_path', label: 'Jugement' },
            { key: 'doc_autres_path', label: 'Autres pièces' },
        ],
        deces: [
            { key: 'doc_death_cert_path', label: 'Certificat de décès' },
            { key: 'doc_deceased_id_path', label: 'Pièce d\'identité défunt' },
            { key: 'doc_declarant_id_path', label: 'CNI Déclarant' },
            { key: 'doc_jugement_path', label: 'Jugement' },
            { key: 'doc_autres_path', label: 'Autres pièces' },
        ],
    }
    return (map[props.type] ?? []).filter(d => a[d.key])
})

import { computed } from 'vue'

const witnesses = computed(() => props.act?.witnesses_metadata ?? [])
const parentsData = computed(() => props.act?.parents_metadata ?? null)
const deathMeta = computed(() => props.act?.death_metadata ?? null)
const spousesMeta = computed(() => props.act?.spouses_metadata ?? null)
</script>

<template>
    <Head :title="`${config.label} — Vérification Officielle`" />
    <GuestLayout>
        <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

            <!-- Retour -->
            <Link :href="route('certificates.verify')" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-[#1E690F] transition-colors group mb-8">
                <ArrowLeftIcon class="h-4 w-4 group-hover:-translate-x-1 transition-transform" />
                Nouvelle vérification
            </Link>

            <div class="bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 overflow-hidden">

                <!-- HEADER -->
                <div :class="`bg-gradient-to-br ${config.color} px-8 py-12 text-white text-center relative overflow-hidden`">
                    <div class="absolute inset-0 opacity-10"
                         style="background-image: repeating-linear-gradient(45deg,white 0,white 1px,transparent 0,transparent 50%); background-size:24px 24px;"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-[0.2em] mb-6">
                            <CheckBadgeIcon class="h-4 w-4" />
                            Document Authentique et Validé
                        </div>
                        <h1 class="text-3xl font-black tracking-tight mb-2">{{ config.label }}</h1>
                        <p class="text-white/70 font-bold tracking-widest text-xs uppercase">Réf : {{ act.reference_number }}</p>
                        
                        <!-- Button to download extract -->
                        <div class="mt-6">
                            <a :href="route('acts.verify.download', { type: type, uuid: act.uuid })" target="_blank"
                               class="inline-flex items-center gap-2 px-6 py-3 bg-white text-gray-900 rounded-full font-black text-xs uppercase tracking-wider hover:bg-gray-100 hover:scale-105 active:scale-95 transition-all shadow-lg">
                                <ArrowDownTrayIcon class="h-4 w-4 text-[#1E690F] shrink-0" />
                                Télécharger l'Extrait PDF
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-8 md:p-12 space-y-10">

                    <!-- ══ NAISSANCE ══ -->
                    <template v-if="type === 'naissance'">
                        <!-- Identité -->
                        <section>
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Identité du nouveau-né</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Prénom(s) &amp; Nom</p>
                                    <p class="text-lg font-black text-gray-900 uppercase">{{ act.first_name }} {{ act.last_name }}</p>
                                </div>
                                <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Sexe</p>
                                    <p class="font-bold text-gray-800">{{ formatGender(act.gender) }}</p>
                                </div>
                                <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Date de naissance</p>
                                    <p class="font-bold text-gray-800">{{ formatDate(act.date_of_birth) }}</p>
                                </div>
                                <div v-if="act.time_of_birth" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Heure de naissance</p>
                                    <p class="font-bold text-gray-800">{{ act.time_of_birth }}</p>
                                </div>
                                <div v-if="act.place_of_birth" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Lieu de naissance</p>
                                    <p class="font-bold text-gray-800">{{ act.place_of_birth }}</p>
                                </div>
                                <div v-if="act.health_facility" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Formation sanitaire</p>
                                    <p class="font-bold text-gray-800">{{ act.health_facility }}</p>
                                </div>
                            </div>
                        </section>

                        <!-- Filiation -->
                        <section v-if="parentsData">
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Filiation</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div v-if="parentsData.father" class="p-5 bg-emerald-50 rounded-2xl border border-emerald-100">
                                    <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-2">Père</p>
                                    <p class="font-black text-gray-900">{{ parentsData.father.first_name }} {{ parentsData.father.last_name }}</p>
                                    <p v-if="parentsData.father.profession" class="text-sm text-gray-500 mt-1">{{ parentsData.father.profession }}</p>
                                </div>
                                <div v-if="parentsData.mother" class="p-5 bg-emerald-50 rounded-2xl border border-emerald-100">
                                    <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-2">Mère</p>
                                    <p class="font-black text-gray-900">{{ parentsData.mother.first_name }} {{ parentsData.mother.last_name }}</p>
                                    <p v-if="parentsData.mother.profession" class="text-sm text-gray-500 mt-1">{{ parentsData.mother.profession }}</p>
                                </div>
                            </div>
                        </section>
                    </template>

                    <!-- ══ MARIAGE ══ -->
                    <template v-else-if="type === 'mariage'">
                        <section>
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Les époux</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div class="p-6 bg-rose-50 rounded-2xl border border-rose-100">
                                    <p class="text-[9px] font-black text-rose-500 uppercase tracking-widest mb-2">Époux</p>
                                    <p class="font-black text-gray-900 text-lg uppercase">{{ act.husband_first_name }} {{ act.husband_last_name }}</p>
                                    <p v-if="spousesMeta?.husband?.date_of_birth" class="text-sm text-gray-500 mt-1">Né le {{ formatDate(spousesMeta.husband.date_of_birth) }}</p>
                                    <p v-if="spousesMeta?.husband?.profession" class="text-sm text-gray-500">{{ spousesMeta.husband.profession }}</p>
                                </div>
                                <div class="p-6 bg-rose-50 rounded-2xl border border-rose-100">
                                    <p class="text-[9px] font-black text-rose-500 uppercase tracking-widest mb-2">Épouse</p>
                                    <p class="font-black text-gray-900 text-lg uppercase">{{ act.wife_first_name }} {{ act.wife_last_name }}</p>
                                    <p v-if="spousesMeta?.wife?.date_of_birth" class="text-sm text-gray-500 mt-1">Née le {{ formatDate(spousesMeta.wife.date_of_birth) }}</p>
                                    <p v-if="spousesMeta?.wife?.profession" class="text-sm text-gray-500">{{ spousesMeta.wife.profession }}</p>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Détails du mariage</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Date</p>
                                    <p class="font-bold text-gray-800">{{ formatDate(act.marriage_date) }}</p>
                                </div>
                                <div v-if="act.marriage_place" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Lieu</p>
                                    <p class="font-bold text-gray-800">{{ act.marriage_place }}</p>
                                </div>
                                <div v-if="act.marriage_option" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Option matrimoniale</p>
                                    <p class="font-bold text-gray-800">{{ formatOption(act.marriage_option) }}</p>
                                </div>
                                <div v-if="act.matrimonial_regime" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Régime matrimonial</p>
                                    <p class="font-bold text-gray-800">{{ formatRegime(act.matrimonial_regime) }}</p>
                                </div>
                            </div>
                        </section>

                        <!-- Témoins mariage -->
                        <section v-if="witnesses.length">
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Témoins ({{ witnesses.length }})</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div v-for="(w, i) in witnesses" :key="i" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-white text-sm shrink-0" style="background: linear-gradient(135deg, #1E690F, #3D9426);">{{ i + 1 }}</div>
                                    <div>
                                        <p class="font-black text-gray-900 text-sm">{{ w.first_name }} {{ w.last_name }}</p>
                                        <p v-if="w.profession" class="text-xs text-gray-500">{{ w.profession }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </template>

                    <!-- ══ DÉCÈS ══ -->
                    <template v-else-if="type === 'deces'">
                        <section>
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Identité du défunt</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Prénom(s) &amp; Nom</p>
                                    <p class="text-lg font-black text-gray-900 uppercase">{{ act.deceased_first_name }} {{ act.deceased_last_name }}</p>
                                </div>
                                <div v-if="act.gender" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Sexe</p>
                                    <p class="font-bold text-gray-800">{{ formatGender(act.gender) }}</p>
                                </div>
                                <div v-if="act.date_of_birth" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Date de naissance</p>
                                    <p class="font-bold text-gray-800">{{ formatDate(act.date_of_birth) }}</p>
                                </div>
                                <div class="p-5 bg-slate-100 rounded-2xl border border-slate-200">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Date de décès</p>
                                    <p class="font-bold text-gray-800">{{ formatDate(act.date_of_death) }}</p>
                                </div>
                                <div v-if="act.time_of_death" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Heure de décès</p>
                                    <p class="font-bold text-gray-800">{{ act.time_of_death }}</p>
                                </div>
                                <div v-if="act.place_of_death" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Lieu de décès</p>
                                    <p class="font-bold text-gray-800">{{ act.place_of_death }}</p>
                                </div>
                            </div>
                        </section>

                        <!-- Parents du défunt -->
                        <section v-if="deathMeta?.father || deathMeta?.mother">
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Filiation</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div v-if="deathMeta.father" class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2">Père</p>
                                    <p class="font-black text-gray-900">{{ deathMeta.father.first_name }} {{ deathMeta.father.last_name }}</p>
                                </div>
                                <div v-if="deathMeta.mother" class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2">Mère</p>
                                    <p class="font-black text-gray-900">{{ deathMeta.mother.first_name }} {{ deathMeta.mother.last_name }}</p>
                                </div>
                            </div>
                        </section>

                        <!-- Déclarant -->
                        <section v-if="deathMeta?.declarant">
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Déclarant</h2>
                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                <p class="font-black text-gray-900">{{ deathMeta.declarant.first_name }} {{ deathMeta.declarant.last_name }}</p>
                                <p v-if="deathMeta.declarant.relationship" class="text-sm text-gray-500 mt-1">{{ deathMeta.declarant.relationship }}</p>
                                <p v-if="deathMeta.declarant.profession" class="text-sm text-gray-500">{{ deathMeta.declarant.profession }}</p>
                            </div>
                        </section>

                        <!-- Témoins décès -->
                        <section v-if="witnesses.length">
                            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Témoins ({{ witnesses.length }})</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div v-for="(w, i) in witnesses" :key="i" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-white text-sm shrink-0" style="background: linear-gradient(135deg, #64748b, #374151);">{{ i + 1 }}</div>
                                    <div>
                                        <p class="font-black text-gray-900 text-sm">{{ w.first_name }} {{ w.last_name }}</p>
                                        <p v-if="w.profession" class="text-xs text-gray-500">{{ w.profession }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </template>

                    <!-- ══ JUGEMENT (commun) ══ -->
                    <section v-if="act.is_judgment">
                        <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Jugement d'autorisation</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div v-if="act.judgment_number" class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                                <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-1">N° Jugement</p>
                                <p class="font-bold text-gray-800">{{ act.judgment_number }}</p>
                            </div>
                            <div v-if="act.judgment_date" class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                                <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-1">Date</p>
                                <p class="font-bold text-gray-800">{{ formatDate(act.judgment_date) }}</p>
                            </div>
                            <div v-if="act.judgment_court" class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                                <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-1">Juridiction</p>
                                <p class="font-bold text-gray-800">{{ act.judgment_court }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- ══ VALIDATION ══ -->
                    <section>
                        <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Validation Registre</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="p-5 bg-green-50 rounded-2xl border border-green-100 flex items-center gap-4">
                                <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center shrink-0">
                                    <CheckBadgeIcon class="h-6 w-6 text-white" />
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-green-600 uppercase tracking-widest">Statut</p>
                                    <p class="font-black text-green-800 text-sm uppercase">Acte Validé et Officiel</p>
                                </div>
                            </div>
                            <div v-if="act.act_registration_date || act.validated_at" class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Date d'inscription</p>
                                <p class="font-bold text-gray-800">{{ formatDate(act.act_registration_date ?? act.validated_at) }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- ══ PIÈCES JUSTIFICATIVES ══ -->
                    <section v-if="docs.length">
                        <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-3 mb-6">Pièces justificatives</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <a v-for="doc in docs" :key="doc.key"
                               :href="act[doc.key].startsWith('/storage') ? act[doc.key] : `/storage/${act[doc.key]}`" target="_blank"
                               class="flex items-center gap-3 p-4 bg-gray-50 border border-gray-200 rounded-2xl hover:bg-[#1E690F] hover:border-[#1E690F] hover:text-white transition-all duration-200 group">
                                <PaperClipIcon class="h-4 w-4 text-gray-400 group-hover:text-white shrink-0" />
                                <span class="text-xs font-bold text-gray-700 group-hover:text-white leading-tight">{{ doc.label }}</span>
                            </a>
                        </div>
                    </section>

                    <!-- ══ NOTE ══ -->
                    <div class="p-6 bg-amber-50 rounded-2xl border border-amber-100 flex items-start gap-4">
                        <ShieldCheckIcon class="h-6 w-6 text-amber-600 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-sm font-bold text-amber-900 mb-1">Note de conformité</p>
                            <p class="text-xs text-amber-800 leading-relaxed">Ce document numérique constitue une preuve officielle dans la limite des informations enregistrées au registre de l'État Civil. Toute altération rend ce document caduc.</p>
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
