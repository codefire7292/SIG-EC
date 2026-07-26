<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    FingerPrintIcon,
    BuildingLibraryIcon,
    ShieldCheckIcon,
    InboxArrowDownIcon,
    BookOpenIcon,
    DocumentCheckIcon,
    ArrowRightIcon,
    SparklesIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    type: String,
    stats: Object,
});

const page = usePage();

const userPermissions = computed(() => page.props.auth.user.permissions || []);
const canViewRegistries = computed(() => userPermissions.value.includes('view-registries'));
const canCreateDrafts = computed(() => userPermissions.value.includes('create-drafts'));
const canManageRegistries = computed(() => userPermissions.value.includes('manage-registries'));

// ─── Type-specific metadata ───────────────────────────────────────────────────
const typeConfig = computed(() => {
    switch (props.type) {
        case 'naissance':
            return {
                label: 'Naissances',
                sublabel: 'Registre des Actes de Naissance',
                icon: FingerPrintIcon,
                gradient: 'from-sky-500 to-indigo-600',
                lightBg: 'bg-sky-50',
                lightBorder: 'border-sky-100',
                accent: '#3B82F6',
                accentLight: 'rgba(59,130,246,0.08)',
                badge: 'Naissance',
            };
        case 'mariage':
            return {
                label: 'Mariages',
                sublabel: 'Registre des Actes de Mariage',
                icon: BuildingLibraryIcon,
                gradient: 'from-rose-500 to-pink-600',
                lightBg: 'bg-rose-50',
                lightBorder: 'border-rose-100',
                accent: '#EC4899',
                accentLight: 'rgba(236,72,153,0.08)',
                badge: 'Mariage',
            };
        case 'deces':
            return {
                label: 'Décès',
                sublabel: 'Registre des Actes de Décès',
                icon: ShieldCheckIcon,
                gradient: 'from-slate-600 to-gray-800',
                lightBg: 'bg-slate-50',
                lightBorder: 'border-slate-100',
                accent: '#475569',
                accentLight: 'rgba(71,85,105,0.08)',
                badge: 'Décès',
            };
        default:
            return {
                label: 'État Civil',
                sublabel: 'Registres',
                icon: FingerPrintIcon,
                gradient: 'from-green-600 to-emerald-700',
                lightBg: 'bg-green-50',
                lightBorder: 'border-green-100',
                accent: '#16a34a',
                accentLight: 'rgba(22,163,74,0.08)',
                badge: 'Acte',
            };
    }
});

// ─── Dossiers (cards) ─────────────────────────────────────────────────────────
const folders = computed(() => [
    {
        id: 'declarations',
        icon: InboxArrowDownIcon,
        emoji: '📥',
        title: 'Déclarations et établissement des actes',
        description: 'Saisir, enregistrer et gérer les actes d\'état civil. Suivi complet du cycle de vie de chaque acte.',
        href: `/acts/${props.type}/list`,
        show: canViewRegistries.value || canCreateDrafts.value,
        stats: [
            { label: 'Total actes', value: props.stats?.total_acts ?? '—' },
            { label: `En ${new Date().getFullYear()}`, value: props.stats?.acts_this_year ?? '—' },
        ],
        color: '#3B82F6',
        colorLight: 'rgba(59,130,246,0.07)',
        colorBorder: 'rgba(59,130,246,0.15)',
        gradientFrom: '#3B82F6',
        gradientTo: '#6366F1',
        cta: 'Ouvrir le registre',
    },
    {
        id: 'registres',
        icon: BookOpenIcon,
        emoji: '📚',
        title: 'Registres',
        description: 'Consulter, créer et administrer les volumes de registres. Gestion des statuts d\'ouverture et de clôture.',
        href: `/admin/registries`,
        show: canManageRegistries.value,
        stats: [
            { label: 'Volumes ouverts', value: props.stats?.open_registries ?? '—' },
            { label: 'Total volumes', value: props.stats?.total_registries ?? '—' },
        ],
        color: '#10B981',
        colorLight: 'rgba(16,185,129,0.07)',
        colorBorder: 'rgba(16,185,129,0.15)',
        gradientFrom: '#10B981',
        gradientTo: '#0D9488',
        cta: 'Gérer les registres',
    },
    {
        id: 'actes-delivres',
        icon: DocumentCheckIcon,
        emoji: '📄',
        title: 'Actes délivrés',
        description: 'Historique des extraits et actes délivrés aux administrés. Suivi des certifications et délivrance officielle.',
        href: `/civil-certificates`,
        show: true,
        stats: [
            { label: 'Actes délivrés', value: props.stats?.total_certificates ?? '—' },
            { label: 'Cette année', value: props.stats?.certificates_this_year ?? '—' },
        ],
        color: '#F59E0B',
        colorLight: 'rgba(245,158,11,0.07)',
        colorBorder: 'rgba(245,158,11,0.15)',
        gradientFrom: '#F59E0B',
        gradientTo: '#EF4444',
        cta: 'Voir les délivrances',
    },
]);

const visibleFolders = computed(() => folders.value.filter(f => f.show));

const pageTitle = computed(() => `${typeConfig.value.label} — État Civil`);
</script>

<template>
    <Head :title="pageTitle" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div
                    class="h-12 w-12 rounded-2xl flex items-center justify-center shadow-lg"
                    :style="`background: linear-gradient(135deg, ${typeConfig.gradientFrom ?? typeConfig.accent}, ${typeConfig.gradientTo ?? typeConfig.accent});`"
                >
                    <component :is="typeConfig.icon" class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h2 class="font-black text-2xl text-gray-900 tracking-tight">{{ typeConfig.label }}</h2>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ typeConfig.sublabel }}</p>
                </div>
            </div>
        </template>

        <div class="space-y-8">

            <!-- ── Hero Banner ─────────────────────────────────────────── -->
            <div
                class="relative overflow-hidden rounded-3xl text-white shadow-xl"
                :style="`background: linear-gradient(135deg, ${typeConfig.gradientFrom ?? typeConfig.accent} 0%, ${typeConfig.gradientTo ?? typeConfig.accent} 100%);`"
            >
                <!-- Decorative circles -->
                <div class="absolute -top-10 -right-10 h-48 w-48 rounded-full opacity-10 bg-white"></div>
                <div class="absolute -bottom-8 -left-8 h-36 w-36 rounded-full opacity-10 bg-white"></div>
                <div class="absolute top-4 right-32 h-20 w-20 rounded-full opacity-5 bg-white"></div>

                <div class="relative px-8 py-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="h-16 w-16 bg-white/15 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/20 shadow-inner flex-shrink-0">
                            <component :is="typeConfig.icon" class="h-9 w-9 text-white" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <SparklesIcon class="h-4 w-4 text-white/70" />
                                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-white/70">Module État Civil</span>
                            </div>
                            <h1 class="text-3xl font-black tracking-tight">{{ typeConfig.label }}</h1>
                            <p class="text-white/70 text-sm font-medium mt-1">{{ typeConfig.sublabel }}</p>
                        </div>
                    </div>

                    <!-- Quick stats pills -->
                    <div class="flex flex-wrap gap-3">
                        <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center">
                            <div class="text-2xl font-black">{{ stats?.total_acts ?? '—' }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-white/70">Actes total</div>
                        </div>
                        <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center">
                            <div class="text-2xl font-black">{{ stats?.acts_this_year ?? '—' }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-white/70">Cette année</div>
                        </div>
                        <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center">
                            <div class="text-2xl font-black">{{ stats?.open_registries ?? '—' }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-white/70">Registres ouverts</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Section Title ───────────────────────────────────────── -->
            <div class="flex items-center gap-3">
                <ChartBarIcon class="h-5 w-5 text-gray-400" />
                <span class="text-xs font-black uppercase tracking-[0.25em] text-gray-400">Dossiers disponibles</span>
                <div class="flex-1 h-px bg-gray-100"></div>
                <span class="text-[10px] font-bold text-gray-300">{{ visibleFolders.length }} section{{ visibleFolders.length > 1 ? 's' : '' }}</span>
            </div>

            <!-- ── Folder Cards ────────────────────────────────────────── -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <Link
                    v-for="folder in visibleFolders"
                    :key="folder.id"
                    :href="folder.href"
                    class="group relative block rounded-3xl border bg-white overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
                    :style="`border-color: ${folder.colorBorder};`"
                >
                    <!-- Top gradient bar -->
                    <div
                        class="h-1.5 w-full"
                        :style="`background: linear-gradient(90deg, ${folder.gradientFrom}, ${folder.gradientTo});`"
                    ></div>

                    <!-- Folder tab (top-left) -->
                    <div
                        class="absolute top-0 left-6 h-6 w-20 rounded-b-lg flex items-center justify-center"
                        :style="`background: linear-gradient(135deg, ${folder.gradientFrom}, ${folder.gradientTo});`"
                    >
                        <span class="text-[9px] font-black text-white uppercase tracking-widest">Dossier</span>
                    </div>

                    <div class="p-7 pt-8">
                        <!-- Icon -->
                        <div
                            class="h-14 w-14 rounded-2xl flex items-center justify-center mb-5 transition-transform duration-300 group-hover:scale-110"
                            :style="`background: linear-gradient(135deg, ${folder.gradientFrom}, ${folder.gradientTo}); box-shadow: 0 8px 24px ${folder.colorLight};`"
                        >
                            <span class="text-2xl">{{ folder.emoji }}</span>
                        </div>

                        <!-- Title & description -->
                        <h3 class="font-black text-gray-900 text-lg leading-snug mb-2 group-hover:text-gray-800 transition-colors">
                            {{ folder.title }}
                        </h3>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed">
                            {{ folder.description }}
                        </p>

                        <!-- Stats pills -->
                        <div class="flex flex-wrap gap-2 mt-5">
                            <div
                                v-for="stat in folder.stats"
                                :key="stat.label"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold"
                                :style="`background: ${folder.colorLight}; border-color: ${folder.colorBorder}; color: ${folder.color};`"
                            >
                                <span class="font-black text-sm">{{ stat.value }}</span>
                                <span class="text-[10px] uppercase tracking-wider opacity-70">{{ stat.label }}</span>
                            </div>
                        </div>

                        <!-- CTA row -->
                        <div
                            class="mt-6 flex items-center justify-between pt-5 border-t"
                            :style="`border-color: ${folder.colorBorder};`"
                        >
                            <span class="text-xs font-black uppercase tracking-widest" :style="`color: ${folder.color};`">
                                {{ folder.cta }}
                            </span>
                            <div
                                class="h-8 w-8 rounded-xl flex items-center justify-center transition-all duration-300 group-hover:translate-x-1"
                                :style="`background: linear-gradient(135deg, ${folder.gradientFrom}, ${folder.gradientTo});`"
                            >
                                <ArrowRightIcon class="h-4 w-4 text-white stroke-[2.5]" />
                            </div>
                        </div>
                    </div>

                    <!-- Hover glow overlay -->
                    <div
                        class="absolute inset-0 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                        :style="`background: radial-gradient(ellipse at top left, ${folder.colorLight}, transparent 60%);`"
                    ></div>
                </Link>
            </div>

            <!-- ── Bottom tip ──────────────────────────────────────────── -->
            <div class="bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 flex items-center gap-3">
                <div class="h-8 w-8 rounded-xl flex items-center justify-center flex-shrink-0" :style="`background: ${typeConfig.accentLight ?? 'rgba(59,130,246,0.08)'};`">
                    <span class="text-base">💡</span>
                </div>
                <p class="text-xs text-gray-500 font-medium leading-relaxed">
                    Sélectionnez un dossier ci-dessus pour accéder aux fonctionnalités correspondantes du module
                    <span class="font-black text-gray-700">{{ typeConfig.label }}</span>.
                    Chaque section est dédiée à une étape précise du cycle documentaire.
                </p>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
