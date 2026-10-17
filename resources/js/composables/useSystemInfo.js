import { ref } from 'vue';

export function useSystemInfo() {
    const title = ref('');
    const content = ref('');
    const open = ref(false);

    async function show(name, url) {
        title.value = name;
        content.value = 'Loading…';
        open.value = true;

        try {
            const response = await fetch(url);
            const body = await response.text();

            content.value = response.headers.get('Content-Type')?.includes('json') ? JSON.stringify(JSON.parse(body), null, 2) : body;
        } catch {
            content.value = 'The server is not reachable.';
        }
    }

    return { title, content, open, showHealth: () => show('Health', '/api/health'), showMetrics: () => show('Metrics', '/api/metrics') };
}
