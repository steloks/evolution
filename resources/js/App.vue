<script setup>
import { onMounted } from 'vue';
import { useRanking } from './composables/useRanking';

const props = defineProps({ firstDay: String, lastDay: String });

const { hasData, range, fromHour, toHour, days, result, errors, loading, isAvailable, search } = useRanking(props.firstDay, props.lastDay);

const hourLabel = (hour) => `${String(hour).padStart(2, '0')}:00`;
const fromHours = Array.from({ length: 24 }, (_, hour) => ({ label: hourLabel(hour), value: hour }));
const toHours = Array.from({ length: 24 }, (_, hour) => ({ label: hourLabel(hour + 1), value: hour + 1 }));

const columns = [
    { name: 'rank', label: '#', field: 'rank', align: 'left' },
    { name: 'name', label: 'City', field: 'name', align: 'left' },
    { name: 'pm25', label: 'PM2.5 µg/m³', field: 'pm25' },
    { name: 'sensors', label: 'Sensors', field: 'sensors' },
    { name: 'coverage', label: 'Coverage', field: 'coverage', format: (value) => `${value}%` },
];

onMounted(() => hasData && search());
</script>

<template>
    <div class="q-pa-md" style="max-width: 860px; margin: 0 auto">
        <div class="text-h4 q-mt-md">Clean Air Finder</div>
        <div class="text-grey-7">Bulgarian cities ranked by average PM2.5 for the chosen period.</div>

        <q-banner v-if="!hasData" class="bg-warning q-mt-md" rounded>
            No data yet. The first import is running, try again in a few minutes.
        </q-banner>

        <div v-else class="row q-col-gutter-md q-mt-sm">
            <div class="col-12 col-sm-auto">
                <q-date v-model="range" range minimal flat bordered color="positive" :options="isAvailable" :navigation-min-year-month="firstDay.slice(0, 7)" :navigation-max-year-month="lastDay.slice(0, 7)" />
            </div>

            <div class="col-12 col-sm column q-gutter-md">
                <div class="text-subtitle1">
                    <template v-if="days">{{ days.from }} {{ hourLabel(fromHour) }} → {{ days.to }} {{ hourLabel(toHour) }}</template>
                    <template v-else>Pick a period in the calendar</template>
                </div>
                <div class="row q-col-gutter-sm">
                    <q-select v-model="fromHour" :options="fromHours" label="From hour" outlined dense emit-value map-options class="col" />
                    <q-select v-model="toHour" :options="toHours" label="To hour" outlined dense emit-value map-options class="col" />
                </div>
                <q-btn label="Find cleanest city" color="positive" unelevated :loading="loading" @click="search" />
                <div class="text-caption text-grey-7">Data from {{ firstDay }} to {{ lastDay }}, Bulgarian local time. The end hour is not included.</div>
            </div>
        </div>

        <q-banner v-for="error in errors" :key="error" class="bg-negative text-white q-mt-md" rounded>
            {{ error }}
        </q-banner>

        <template v-if="result">
            <q-banner v-if="!result.cities.length" class="bg-grey-3 q-mt-md" rounded>
                No measurements for this period.
            </q-banner>

            <template v-else>
                <q-card flat bordered class="bg-green-1 q-mt-md">
                    <q-card-section>
                        <div class="text-caption text-positive">Cleanest air</div>
                        <div class="text-h5">{{ result.cities[0].name }}</div>
                        <div>{{ result.cities[0].pm25 }} µg/m³ average PM2.5</div>
                    </q-card-section>
                </q-card>

                <q-table
                    class="q-mt-md"
                    flat
                    bordered
                    :rows="result.cities"
                    :columns="columns"
                    row-key="name"
                    :pagination="{ rowsPerPage: 0 }"
                    hide-pagination
                />
            </template>

            <div class="text-caption text-grey-7 q-mt-sm">{{ result.hours }} hours, query took {{ result.query_ms }} ms.</div>
        </template>
    </div>
</template>
