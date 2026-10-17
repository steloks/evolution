import { computed, ref } from 'vue';
import { date } from 'quasar';

const DAY = 'YYYY/MM/DD';

const toApi = (day, hour) => date.formatDate(date.addToDate(date.extractDate(day, DAY), { hours: hour }), 'YYYY-MM-DDTHH:00');

export function useRanking(firstDay, lastDay) {
    const hasData = Boolean(firstDay && lastDay);
    const weekBeforeLast = hasData ? date.formatDate(date.subtractFromDate(date.extractDate(lastDay, DAY), { days: 6 }), DAY) : null;

    const range = ref(hasData ? { from: weekBeforeLast < firstDay ? firstDay : weekBeforeLast, to: lastDay } : null);
    const fromHour = ref(0);
    const toHour = ref(24);
    const result = ref(null);
    const errors = ref([]);
    const loading = ref(false);

    const days = computed(() => (typeof range.value === 'string' ? { from: range.value, to: range.value } : range.value));

    const isAvailable = (day) => day >= firstDay && day <= lastDay;

    async function search() {
        if (!days.value) {
            errors.value = ['Pick a period in the calendar.'];
            return;
        }

        loading.value = true;
        errors.value = [];

        try {
            const query = new URLSearchParams({ from: toApi(days.value.from, fromHour.value), to: toApi(days.value.to, toHour.value) });
            const response = await fetch(`/api/rankings?${query}`, { headers: { Accept: 'application/json' } });
            const body = await response.json();

            if (response.ok) {
                result.value = { ...body, cities: body.cities.map((city, index) => ({ ...city, rank: index + 1 })) };
            } else {
                result.value = null;
                errors.value = body.errors ? Object.values(body.errors).flat() : [body.message];
            }
        } catch {
            result.value = null;
            errors.value = ['The server is not reachable. Try again later.'];
        } finally {
            loading.value = false;
        }
    }

    return { hasData, range, fromHour, toHour, days, result, errors, loading, isAvailable, search };
}
