<script>

import PersonForm from "./PersonForm.vue";
import {trans} from "laravel-vue-i18n";

export default {
    extends: PersonForm,
    data() {
        return {
            storeButton: {
                text: 'app.common.create',
                color: 'primary',
                icon: 'bi-clipboard-check',
            },
            titleKey: trans('app.person.create_title'),
        }
    },
    methods: {
        init() {
            this.getClubs();
        },
        updatePerson() {
            axios.post('/api/frontend/person', this.person)
                .then(response => {
                    this.person = response.data.data;
                    this.$router.back()
                })
                .catch(error => {
                    if (error.response.data.errors) {
                        this.errors = error.response.data.errors;
                    }
                });
        },
    }
};
</script>
