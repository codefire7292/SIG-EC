<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ClipboardDocumentCheckIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    UserCircleIcon,
    EyeIcon,
    CalendarIcon,
    ShieldCheckIcon,
    ClockIcon,
    GlobeAltIcon,
    DocumentTextIcon,
    FunnelIcon,
    CheckCircleIcon,
    XCircleIcon,
    PencilSquareIcon,
    PlusCircleIcon,
    ExclamationTriangleIcon,
    BookOpenIcon,
    ArrowRightOnRectangleIcon,
    ArrowLeftOnRectangleIcon,
    SparklesIcon,
    ComputerDesktopIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    logs: Object,
    stats: Object,
    filters: Object,
    agents: Array,
    availableActions: Array,
    availableTypes: Array,
});

// Filter states
const search = ref(props.filters?.search || '');
const agentFilter = ref(props.filters?.user_id || 'all');
const actionFilter = ref(props.filters?.action || 'all');
const typeFilter = ref(props.filters?.target_type || 'all');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

// Inspection modal state
const selectedLog = ref(null);
const isModalOpen = ref(false);
const showRawJson = ref(false);

let searchDebounce = null;
const triggerSearch = () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        applyFilters();
    }, 350);
};

watch(search, () => triggerSearch());

const applyFilters = () => {
    const params = {};
    if (search.value.trim()) params.search = search.value.trim();
    if (agentFilter.value && agentFilter.value !== 'all') params.user_id = agentFilter.value;
    if (actionFilter.value && actionFilter.value !== 'all') params.action = actionFilter.value;
    if (typeFilter.value && typeFilter.value !== 'all') params.target_type = typeFilter.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;

    router.get('/admin/audit-logs', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    agentFilter.value = 'all';
    actionFilter.value = 'all';
    typeFilter.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

const hasActiveFilters = computed(() => {
    return Boolean(
        search.value.trim() ||
        (agentFilter.value && agentFilter.value !== 'all') ||
        (actionFilter.value && actionFilter.value !== 'all') ||
        (typeFilter.value && typeFilter.value !== 'all') ||
        dateFrom.value ||
        dateTo.value
    );
});

const exportCsv = () => {
    const params = new URLSearchParams();
    if (search.value.trim()) params.append('search', search.value.trim());
    if (agentFilter.value && agentFilter.value !== 'all') params.append('user_id', agentFilter.value);
    if (actionFilter.value && actionFilter.value !== 'all') params.append('action', actionFilter.value);
    if (typeFilter.value && typeFilter.value !== 'all') params.append('target_type', typeFilter.value);
    if (dateFrom.value) params.append('date_from', dateFrom.value);
    if (dateTo.value) params.append('date_to', dateTo.value);

    window.location.href = `/admin/audit-logs/export?${params.toString()}`;
};

const openInspectionModal = (log) => {
    selectedLog.value = log;
    showRawJson.value = false;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedLog.value = null;
    showRawJson.value = false;
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

const getRelativeTime = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diffSeconds = Math.floor((now - date) / 1000);

    if (diffSeconds < 60) return "À l'instant";
    const diffMinutes = Math.floor(diffSeconds / 60);
    if (diffMinutes < 60) return `Il y a ${diffMinutes} min`;
    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) return `Il y a ${diffHours} h`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays === 1) return 'Hier';
    if (diffDays < 7) return `Il y a ${diffDays} j`;
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
};

const getInitials = (name) => {
    if (!name) return 'AG';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const getRoleBadgeColor = (role) => {
    switch (role?.toLowerCase()) {
        case 'administrateur technique':
            return 'bg-purple-100 text-purple-800 border-purple-200';
        case 'maire ou délégué':
            return 'bg-amber-100 text-amber-800 border-amber-200';
        case "officier d'état-civil":
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'superviseur / chef de centre':
            return 'bg-indigo-100 text-indigo-800 border-indigo-200';
        case "agent d'état-civil":
            return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        default:
            return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

const getBrowserName = (userAgent) => {
    if (!userAgent) return 'Inconnu';
    if (userAgent.includes('Firefox')) return 'Firefox';
    if (userAgent.includes('Chrome')) return 'Chrome';
    if (userAgent.includes('Safari')) return 'Safari';
    if (userAgent.includes('Edge')) return 'Edge';
    return 'Web';
};
</script>

<template>
    <Head title="Module de Contrôle — Traçabilité des Agents" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div 
                        class="p-2.5 rounded-2xl shadow-lg border text-white flex items-center justify-center"
                        style="background: linear-gradient(135deg, #0A2903 0%, #1E690F 100%); border-color: #F0C31E;"
                    >
                        <ClipboardDocumentCheckIcon class="h-7 w-7 text-yellow-400 stroke-[2.2]" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-black text-2xl text-gray-900 tracking-tight">
                                Module de Contrôle des Agents
                            </h2>
                            <span 
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Temps Réel
                            </span>
                        </div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                            Audit & Traçabilité Complète — Qui a fait quoi et quand sur le SIG-EC
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        @click="exportCsv"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 hover:border-emerald-600 rounded-xl font-black text-xs text-gray-700 hover:text-emerald-800 uppercase tracking-wider shadow-sm transition-all active:scale-95 group"
                        title="Télécharger le journal au format CSV (compatible Excel)"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 text-emerald-600 group-hover:translate-y-0.5 transition-transform" />
                        Exporter les Logs (CSV)
                    </button>
                    <button
                        @click="applyFilters"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-xl font-black text-xs text-gray-600 uppercase tracking-wider transition-all active:scale-95"
                        title="Actualiser la liste"
                    >
                        <ArrowPathIcon class="w-4 h-4 text-gray-500" />
                        Actualiser
                    </button>
                </div>
            </div>
        </template>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Activities -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">
                        Total Activités
                    </span>
                    <span class="text-3xl font-black text-gray-900 tracking-tight">
                        {{ stats.total_activities.toLocaleString() }}
                    </span>
                    <span class="text-[11px] font-bold text-gray-400 block mt-0.5">
                        Événements tracés
                    </span>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100">
                    <DocumentTextIcon class="h-6 w-6 stroke-[2.2]" />
                </div>
            </div>

            <!-- Today's Activities -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">
                        Aujourd'hui
                    </span>
                    <span class="text-3xl font-black text-emerald-600 tracking-tight">
                        {{ stats.today_activities.toLocaleString() }}
                    </span>
                    <span class="text-[11px] font-bold text-gray-400 block mt-0.5">
                        Actions ce jour
                    </span>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center border border-amber-100">
                    <ClockIcon class="h-6 w-6 stroke-[2.2]" />
                </div>
            </div>

            <!-- Active Agents -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">
                        Agents Actifs
                    </span>
                    <span class="text-3xl font-black text-blue-600 tracking-tight">
                        {{ stats.active_agents_count }}
                    </span>
                    <span class="text-[11px] font-bold text-gray-400 block mt-0.5">
                        Opérateurs enregistrés
                    </span>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center border border-blue-100">
                    <UserCircleIcon class="h-6 w-6 stroke-[2.2]" />
                </div>
            </div>

            <!-- Validations & Signatures -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">
                        Validations & Signatures
                    </span>
                    <span class="text-3xl font-black text-purple-600 tracking-tight">
                        {{ stats.validated_and_signed_count.toLocaleString() }}
                    </span>
                    <span class="text-[11px] font-bold text-gray-400 block mt-0.5">
                        Actes validés légalement
                    </span>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center border border-purple-100">
                    <ShieldCheckIcon class="h-6 w-6 stroke-[2.2]" />
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm mb-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-4 relative">
                    <MagnifyingGlassIcon class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher par agent, acte N°, adresse IP..."
                        class="w-full pl-10 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
                    />
                    <button
                        v-if="search"
                        @click="search = ''; applyFilters();"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                        <XMarkIcon class="w-4 h-4" />
                    </button>
                </div>

                <!-- Agent Filter -->
                <div class="lg:col-span-3">
                    <select
                        v-model="agentFilter"
                        @change="applyFilters"
                        class="w-full py-2.5 px-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
                    >
                        <option value="all">Tous les agents</option>
                        <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                            {{ agent.name }} ({{ agent.email }})
                        </option>
                    </select>
                </div>

                <!-- Action Filter -->
                <div class="lg:col-span-3">
                    <select
                        v-model="actionFilter"
                        @change="applyFilters"
                        class="w-full py-2.5 px-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
                    >
                        <option v-for="act in availableActions" :key="act.value" :value="act.value">
                            {{ act.label }}
                        </option>
                    </select>
                </div>

                <!-- Module Type Filter -->
                <div class="lg:col-span-2">
                    <select
                        v-model="typeFilter"
                        @change="applyFilters"
                        class="w-full py-2.5 px-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
                    >
                        <option v-for="t in availableTypes" :key="t.value" :value="t.value">
                            {{ t.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Date Range & Reset Row -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Période du :</span>
                        <input
                            v-model="dateFrom"
                            @change="applyFilters"
                            type="date"
                            class="py-1.5 px-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Au :</span>
                        <input
                            v-model="dateTo"
                            @change="applyFilters"
                            type="date"
                            class="py-1.5 px-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 rounded-xl text-xs font-bold transition-all"
                    >
                        <XMarkIcon class="w-3.5 h-3.5 stroke-[2.5]" />
                        Réinitialiser les filtres
                    </button>
                </div>

                <div class="text-[11px] font-bold text-gray-400">
                    Affichage de <span class="text-gray-900 font-black">{{ logs.data.length }}</span> sur <span class="text-gray-900 font-black">{{ logs.total }}</span> événements tracés
                </div>
            </div>
        </div>

        <!-- Main Audit Logs Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/60">
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Date & Heure
                            </th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Agent Responsable
                            </th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Action Réalisée
                            </th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Élément Concerné
                            </th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Adresse IP & Navigateur
                            </th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Inspection
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="log in logs.data"
                            :key="log.id"
                            class="group hover:bg-emerald-50/30 transition-colors"
                        >
                            <!-- Date & Time -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-gray-900">
                                        {{ formatDate(log.created_at) }}
                                    </span>
                                    <span class="text-[10px] font-bold text-gray-400 mt-0.5">
                                        {{ getRelativeTime(log.created_at) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Agent (User) -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-9 w-9 rounded-full flex items-center justify-center font-black text-xs text-emerald-800 border shadow-sm flex-shrink-0"
                                        style="background: #D9EDD0; border-color: #F0C31E;"
                                    >
                                        {{ getInitials(log.agent_name) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-gray-900 truncate">
                                                {{ log.agent_name }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-tight border"
                                                :class="getRoleBadgeColor(log.agent_role)"
                                            >
                                                {{ log.agent_role }}
                                            </span>
                                            <span class="text-[10px] text-gray-400 truncate max-w-[120px]">
                                                {{ log.agent_email }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Action Badge -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm"
                                    :class="log.action_badge.bg"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="log.action_badge.dot"></span>
                                    {{ log.action_label }}
                                </div>
                            </td>

                            <!-- Target / Document Reference -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col max-w-xs">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        {{ log.target_type }}
                                    </span>
                                    <template v-if="log.target_url">
                                        <Link
                                            :href="log.target_url"
                                            class="text-xs font-black text-emerald-700 hover:text-emerald-900 hover:underline truncate mt-0.5"
                                            title="Ouvrir la fiche"
                                        >
                                            {{ log.target_summary }}
                                        </Link>
                                    </template>
                                    <template v-else>
                                        <span class="text-xs font-bold text-gray-800 truncate mt-0.5">
                                            {{ log.target_summary }}
                                        </span>
                                    </template>
                                </div>
                            </td>

                            <!-- IP & User Agent -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1.5">
                                        <GlobeAltIcon class="w-3.5 h-3.5 text-gray-400" />
                                        <span class="text-xs font-mono font-bold text-gray-700">
                                            {{ log.metadata?.ip || '127.0.0.1' }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-medium mt-0.5 truncate max-w-[150px]" :title="log.metadata?.user_agent">
                                        {{ getBrowserName(log.metadata?.user_agent) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Inspect Button -->
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <button
                                    @click="openInspectionModal(log)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-black text-xs text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-all active:scale-95"
                                    title="Inspecter les détails de l'action"
                                >
                                    <EyeIcon class="w-4 h-4 stroke-[2.2]" />
                                    Détails
                                </button>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="logs.data.length === 0">
                            <td colspan="6" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center border border-gray-100 shadow-sm">
                                        <ClipboardDocumentCheckIcon class="h-8 w-8 text-gray-300" />
                                    </div>
                                    <h3 class="text-sm font-black text-gray-800">
                                        Aucun événement d'audit trouvé
                                    </h3>
                                    <p class="text-xs text-gray-400 max-w-sm">
                                        Aucune activité d'agent ne correspond aux filtres appliqués. Essayez d'élargir la période ou de réinitialiser vos critères.
                                    </p>
                                    <button
                                        v-if="hasActiveFilters"
                                        @click="resetFilters"
                                        class="mt-2 px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all"
                                    >
                                        Effacer les filtres
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div
                v-if="logs.links && logs.links.length > 3"
                class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between"
            >
                <div class="text-xs font-bold text-gray-500">
                    Page <span class="font-black text-gray-900">{{ logs.current_page }}</span> sur <span class="font-black text-gray-900">{{ logs.last_page }}</span>
                </div>

                <div class="flex items-center gap-1">
                    <template v-for="(link, i) in logs.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                            :class="link.active
                                ? 'bg-emerald-700 text-white shadow-sm font-black'
                                : 'text-gray-600 hover:bg-gray-200'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-3 py-1.5 text-xs text-gray-300 font-bold"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Deep Inspection Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isModalOpen && selectedLog"
                    class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                >
                    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full border border-gray-100 max-h-[90vh] flex flex-col">
                        <!-- Modal Header -->
                        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/60">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm">
                                    <ShieldCheckIcon class="h-6 w-6 stroke-[2.2]" />
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-gray-900 leading-tight">
                                        Fiche d'Audit #{{ selectedLog.id }}
                                    </h3>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                                        Enregistrement certifié le {{ formatDate(selectedLog.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <button
                                @click="closeModal"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors"
                            >
                                <XMarkIcon class="w-5 h-5 stroke-[2.5]" />
                            </button>
                        </div>

                        <!-- Modal Body (Scrollable) -->
                        <div class="p-6 space-y-6 overflow-y-auto flex-1">
                            <!-- Agent Card -->
                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-2">
                                    Agent Responsable
                                </span>
                                <div class="flex items-center gap-3.5">
                                    <div
                                        class="h-12 w-12 rounded-2xl flex items-center justify-center font-black text-sm text-emerald-900 border shadow-sm"
                                        style="background: #D9EDD0; border-color: #F0C31E;"
                                    >
                                        {{ getInitials(selectedLog.agent_name) }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-gray-900">
                                            {{ selectedLog.agent_name }}
                                        </h4>
                                        <p class="text-xs text-gray-500 font-medium">
                                            {{ selectedLog.agent_email }}
                                        </p>
                                        <div class="mt-1">
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-tight border inline-block"
                                                :class="getRoleBadgeColor(selectedLog.agent_role)"
                                            >
                                                {{ selectedLog.agent_role }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action & Target Summary -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">
                                        Action Effectuée
                                    </span>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm mt-1"
                                        :class="selectedLog.action_badge.bg"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="selectedLog.action_badge.dot"></span>
                                        {{ selectedLog.action_label }}
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">
                                        Module & Cible
                                    </span>
                                    <span class="text-xs font-black text-gray-900 block mt-1">
                                        {{ selectedLog.target_summary }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block mt-0.5">
                                        Type : {{ selectedLog.target_type }}
                                    </span>
                                </div>
                            </div>

                            <!-- Interactive Changes Diff (Modifications) -->
                            <div v-if="selectedLog.formatted_changes && selectedLog.formatted_changes.length > 0" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        Détail des Modifications Apportées
                                    </span>
                                    <span class="text-[10px] font-black text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                        {{ selectedLog.formatted_changes.length }} champ(s) modifié(s)
                                    </span>
                                </div>

                                <div class="rounded-2xl border border-gray-200 overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-100 text-xs">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2.5 text-left font-black text-gray-500 uppercase text-[9px]">Champ</th>
                                                <th class="px-4 py-2.5 text-left font-black text-red-600 uppercase text-[9px]">Ancienne valeur</th>
                                                <th class="px-4 py-2.5 text-left font-black text-emerald-600 uppercase text-[9px]">Nouvelle valeur</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <tr v-for="diff in selectedLog.formatted_changes" :key="diff.field" class="hover:bg-gray-50/50">
                                                <td class="px-4 py-3 font-black text-gray-800">
                                                    {{ diff.label }}
                                                    <span class="block text-[9px] text-gray-400 font-mono">{{ diff.field }}</span>
                                                </td>
                                                <td class="px-4 py-3 bg-red-50/40 text-red-700 font-bold">
                                                    {{ diff.old || '—' }}
                                                </td>
                                                <td class="px-4 py-3 bg-emerald-50/40 text-emerald-800 font-bold">
                                                    {{ diff.new || '—' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Technical Context (IP, User Agent) -->
                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 space-y-2">
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">
                                    Environnement Technique & Réseau
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-[10px] text-gray-400 font-bold block">Adresse IP :</span>
                                        <span class="font-mono font-black text-gray-800">
                                            {{ selectedLog.metadata?.ip || '127.0.0.1' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-gray-400 font-bold block">Navigateur :</span>
                                        <span class="font-bold text-gray-800 truncate block" :title="selectedLog.metadata?.user_agent">
                                            {{ selectedLog.metadata?.user_agent || 'Système local' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Raw JSON Toggle -->
                            <div>
                                <button
                                    type="button"
                                    @click="showRawJson = !showRawJson"
                                    class="text-xs font-bold text-gray-500 hover:text-emerald-700 underline"
                                >
                                    {{ showRawJson ? 'Masquer les données JSON brutes' : 'Voir les données JSON brutes' }}
                                </button>
                                <pre
                                    v-if="showRawJson"
                                    class="mt-2 p-3 bg-gray-900 text-emerald-400 rounded-2xl text-[10px] font-mono overflow-x-auto max-h-48"
                                >{{ JSON.stringify(selectedLog.metadata, null, 2) }}</pre>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="p-6 border-t border-gray-100 flex items-center justify-between bg-gray-50/60">
                            <Link
                                v-if="selectedLog.target_url"
                                :href="selectedLog.target_url"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all active:scale-95"
                            >
                                <EyeIcon class="w-4 h-4 stroke-[2.2]" />
                                Consulter la Fiche
                            </Link>
                            <span v-else class="text-[10px] text-gray-400 italic">
                                Cible non accessible directement
                            </span>

                            <button
                                type="button"
                                @click="closeModal"
                                class="px-6 py-2.5 bg-white border border-gray-200 rounded-xl font-black text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition-all active:scale-95"
                            >
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
