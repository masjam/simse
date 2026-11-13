import { ref, watch } from 'vue';

export function useDebouncedSearch(initialValue = '', delay = 350, onSearch = null) {
    const search = ref(initialValue);
    const debouncedValue = ref(initialValue);
    let timer = null;

    watch(search, (newVal) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            debouncedValue.value = newVal;
            if (typeof onSearch === 'function') {
                onSearch(newVal);
            }
        }, delay);
    });

    function clear() {
        search.value = '';
        debouncedValue.value = '';
        clearTimeout(timer);
    }

    return {
        search,
        debouncedValue,
        clear,
    };
}

