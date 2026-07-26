<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    UserPlusIcon,
    HeartIcon,
    MoonIcon,
    ArchiveBoxIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    LockClosedIcon,
    LockOpenIcon,
    CalendarIcon,
    DocumentTextIcon,
    RectangleStackIcon,
    FolderOpenIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    type: String,
    registries: Array,   // [{id, year, number, status, reference_prefix, opening_date, closing_date, acts_count}]
});

// ─── Type config ──────────────────────────────────────────────────────────────
const typeConfig = computed(() => {
    switch (props.type) {
        case 'naissance':
            return {
                label: 'Naissances',
                sublabel: 'Volumes de registres de naissance',
                icon: UserPlusIcon,
                gradientFrom: '#0EA5E9',
                gradientTo: '#6366F1',
                accent: '#3B82F6',
                accentBg: 'rgba(59,130,246,0.08)',
                accentBorder: 'rgba(59,130,246,0.2)',
            };
        case 'mariage':
            return {
                label: 'Mariages',
                sublabel: 'Volumes de registres de mariage',
                icon: HeartIcon,
                gradientFrom: '#F43F5E',
                gradientTo: '#EC4899',
                accent: '#EC4899',
                accentBg: 'rgba(236,72,153,0.08)',
                accentBorder: 'rgba(236,72,153,0.2)',
            };
        case 'deces':
            return {
                label: 'Décès',
                sublabel: 'Volumes de registres de décès',
                icon: MoonIcon,
                gradientFrom: '#475569',
                gradientTo: '#1E293B',
                accent: '#475569',
                accentBg: 'rgba(71,85,105,0.08)',
                accentBorder: 'rgba(71,85,105,0.2)',
            };
        default:
            return {
                label: 'État Civil',
                sublabel: 'Volumes de registres',
                icon: ArchiveBoxIcon,
                gradientFrom: '#16a34a',
                gradientTo: '#0D9488',
                accent: '#16a34a',
                accentBg: 'rgba(22,163,74,0.08)',
                accentBorder: 'rgba(22,163,74,0.2)',
            };
    }
});

const pageTitle = computed(() => `Registres — ${typeConfig.value.label}`);

// Group registries by year
const byYear = computed(() => {
    const map = {};
    props.registries.forEach(r => {
        if (!map[r.year]) map[r.year] = [];
        map[r.year].push(r);
    });
    // Sort years descending
    return Object.entries(map)
        .sort(([a], [b]) => Number(b) - Number(a))
        .map(([year, regs]) => ({ year: Number(year), regs }));
});

const totalActs = computed(() =>
    props.registries.reduce((sum, r) => sum + (r.acts_count ?? 0), 0)
);
const openCount = computed(() =>
    props.registries.filter(r => r.status === 'open').length
);

const formatDate = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head :title="pageTitle" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <Link :href="`/acts/${type}`" class="hover:text-blue-600 transition-colors flex items-center gap-1">
                        <ChevronLeftIcon class="h-3 w-3 stroke-[3]" />
                        {{ typeConfig.label }}
                    </Link>
                    <ChevronRightIcon class="h-3 w-3 stroke-[2]" />
                    <span class="text-gray-600">Registres</span>
                </div>

                <!-- Title row -->
                <div class="flex items-center gap-4">
                    <div
                        class="h-12 w-12 rounded-2xl flex items-center justify-center shadow-lg"
                        :style="`background: linear-gradient(135deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`"
                    >
                        <ArchiveBoxIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-gray-900 tracking-tight">Registres — {{ typeConfig.label }}</h2>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ typeConfig.sublabel }}</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-8">

            <!-- ── Stats banner ────────────────────────────────────────── -->
            <div
                class="relative overflow-hidden rounded-3xl text-white shadow-xl"
                :style="`background: linear-gradient(135deg, ${typeConfig.gradientFrom} 0%, ${typeConfig.gradientTo} 100%);`"
            >
                <div class="absolute -top-8 -right-8 h-40 w-40 rounded-full opacity-10 bg-white"></div>
                <div class="absolute -bottom-6 -left-6 h-28 w-28 rounded-full opacity-10 bg-white"></div>

                <div class="relative px-8 py-7 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                    <div class="flex items-center gap-4">
                        <div class="h-14 w-14 bg-white/15 rounded-2xl flex items-center justify-center border border-white/20 flex-shrink-0">
                            <ArchiveBoxIcon class="h-8 w-8 text-white" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-white/60 mb-0.5">Volumes disponibles</p>
                            <h1 class="text-2xl font-black">{{ registries.length }} volume{{ registries.length > 1 ? 's' : '' }}</h1>
                            <p class="text-white/70 text-xs font-medium mt-0.5">{{ typeConfig.sublabel }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center">
                            <div class="text-2xl font-black">{{ totalActs }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-white/70">Actes total</div>
                        </div>
                        <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center">
                            <div class="text-2xl font-black">{{ openCount }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-white/70">Ouverts</div>
                        </div>
                        <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center">
                            <div class="text-2xl font-black">{{ registries.length - openCount }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-white/70">Clôturés</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Empty state ─────────────────────────────────────────── -->
            <div v-if="registries.length === 0" class="bg-white rounded-3xl border border-gray-100 shadow-sm px-8 py-20 text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center">
                        <ArchiveBoxIcon class="h-8 w-8 text-gray-300" />
                    </div>
                    <div>
                        <p class="font-black text-gray-900 text-lg">Aucun registre trouvé</p>
                        <p class="text-sm text-gray-400 font-medium mt-1">Les registres seront créés automatiquement lors du premier enregistrement d'un acte.</p>
                    </div>
                </div>
            </div>

            <!-- ── Registries grouped by year ──────────────────────────── -->
            <div v-for="group in byYear" :key="group.year" class="space-y-4">

                <!-- Year header -->
                <div class="flex items-center gap-3">
                    <CalendarIcon class="h-4 w-4 text-gray-400" />
                    <span class="text-xs font-black uppercase tracking-[0.25em] text-gray-500">Année {{ group.year }}</span>
                    <div class="flex-1 h-px bg-gray-100"></div>
                    <span class="text-[10px] font-bold text-gray-300">
                        {{ group.regs.length }} volume{{ group.regs.length > 1 ? 's' : '' }}
                    </span>
                </div>

                <!-- Volume cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    <Link
                        v-for="reg in group.regs"
                        :key="reg.id"
                        :href="`/acts/${type}/list?registry_id=${reg.id}`"
                        class="group relative bg-white rounded-2xl border overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl"
                        :style="`border-color: ${reg.status === 'open' ? typeConfig.accentBorder : 'rgba(0,0,0,0.08)'};`"
                    >
                        <!-- Left accent bar -->
                        <div
                            class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl"
                            :style="reg.status === 'open'
                                ? `background: linear-gradient(180deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`
                                : 'background: #e2e8f0;'"
                        ></div>

                        <div class="pl-6 pr-5 py-5">
                            <!-- Header row -->
                            <div class="flex items-start justify-between gap-2 mb-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform duration-300 group-hover:scale-110"
                                        :style="reg.status === 'open'
                                            ? `background: linear-gradient(135deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`
                                            : 'background: #f1f5f9;'"
                                    >
                                        <RectangleStackIcon
                                            class="h-5 w-5"
                                            :class="reg.status === 'open' ? 'text-white' : 'text-slate-400'"
                                        />
                                    </div>
                                    <div>
                                        <p class="font-black text-gray-900 text-sm leading-tight">
                                            Volume {{ reg.number }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 font-mono font-bold uppercase tracking-wider">
                                            {{ reg.reference_prefix ?? '—' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Status badge -->
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest flex-shrink-0"
                                    :style="reg.status === 'open'
                                        ? `background: ${typeConfig.accentBg}; color: ${typeConfig.accent};`
                                        : 'background: rgba(0,0,0,0.04); color: #94a3b8;'"
                                >
                                    <component
                                        :is="reg.status === 'open' ? LockOpenIcon : LockClosedIcon"
                                        class="h-3 w-3"
                                    />
                                    {{ reg.status === 'open' ? 'Ouvert' : 'Clôturé' }}
                                </span>
                            </div>

                            <!-- Acts count -->
                            <div
                                class="flex items-center justify-between rounded-xl px-4 py-3 mb-4"
                                :style="`background: ${reg.status === 'open' ? typeConfig.accentBg : 'rgba(0,0,0,0.03)'};`"
                            >
                                <div class="flex items-center gap-2">
                                    <DocumentTextIcon class="h-4 w-4" :style="`color: ${reg.status === 'open' ? typeConfig.accent : '#94a3b8'};`" />
                                    <span class="text-xs font-bold text-gray-500">Actes enregistrés</span>
                                </div>
                                <span class="text-xl font-black" :style="`color: ${reg.status === 'open' ? typeConfig.accent : '#64748b'};`">
                                    {{ reg.acts_count ?? 0 }}
                                    <span class="text-xs font-bold text-gray-300">/ 50</span>
                                </span>
                            </div>

                            <!-- Dates & CTA -->
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-400 font-medium space-y-0.5">
                                    <div class="flex items-center gap-1">
                                        <span class="font-black text-gray-500">Ouvert le</span>
                                        {{ formatDate(reg.opening_date) }}
                                    </div>
                                    <div v-if="reg.closing_date" class="flex items-center gap-1">
                                        <span class="font-black text-gray-500">Clôturé le</span>
                                        {{ formatDate(reg.closing_date) }}
                                    </div>
                                </div>

                                <div
                                    class="h-8 w-8 rounded-xl flex items-center justify-center transition-all duration-300 group-hover:translate-x-1"
                                    :style="reg.status === 'open'
                                        ? `background: linear-gradient(135deg, ${typeConfig.gradientFrom}, ${typeConfig.gradientTo});`
                                        : 'background: #e2e8f0;'"
                                >
                                    <FolderOpenIcon
                                        class="h-4 w-4"
                                        :class="reg.status === 'open' ? 'text-white' : 'text-slate-400'"
                                    />
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
