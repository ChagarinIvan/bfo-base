<template>
    <h2 id="up">{{ titleKey }}</h2>
    <div class="row">
        <form id="form">
            <div class="form-floating mb-3">
                <input class="form-control"
                       :class="{ 'is-invalid' : this.withLastnameError }"
                       id="lastname"
                       name="lastname"
                       v-model=person.lastname
                />
                <label for="lastname">{{ this.lastnameLabel }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control"
                       id="firstname"
                       name="firstname"
                       v-model=person.firstname
                       :class="{ 'is-invalid' : this.withFirstnameError }"
                />
                <label for="firstname">{{ this.firstnameLabel }}</label>
            </div>
            <div class="form-floating mb-3">
                <input class="form-control"
                       type="date"
                       id="birthday"
                       name="birthday"
                       v-model=person.birthday
                       :class="{ 'is-invalid' : this.withBirthdayError }"
                />
                <label for="birthday">{{ this.birthdayLabel }}</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select"
                        :class="{ 'is-invalid' : this.withClubError }"
                        id="club_id"
                        name="club_id"
                        v-model=person.club_id
                >
                    <option value="0" :selected=this.selectedClub(0)></option>
                    <option v-for="club in this.clubs"
                            :value=club.id
                            :selected=this.selectedClub(club.id)
                    >{{ club.name }}</option>
                </select>
                <label for="club_id">{{ this.clubLabel }}</label>
            </div>
            <div class="col-12">
                <ui-back-button :color=this.storeButton.color
                           :icon=this.storeButton.icon
                           :text=this.storeButton.text
                           @click="this.updatePerson()"
                ></ui-back-button>
                <ui-back-button :color="'danger'"
                           :icon="'bi-reply-fill'"
                           :text="'app.common.back'"
                           @click="$router.back()"
                ></ui-back-button>
            </div>
        </form>
    </div>
</template>

<script>

import UiBackButton from "../UiBackButton.vue";
import {trans} from "laravel-vue-i18n";

export default {
    components: {
        UiBackButton
    },
    data() {
        return {
            person: {
                lastname: null,
                firstname: null,
                birthday: null,
                club_id: null,
            },
            clubs: [],
            errors: {
                firstname: null,
                lastname: null,
                birthday: null,
                club_id: null,
            },
            storeButton: {
                text: 'app.common.edit',
                color: 'primary',
                icon: 'bi-clipboard-check',
            },
            titleKey: trans('app.person.edit_title'),
        }
    },
    mounted() {
        this.init();
    },
    computed: {
        withLastnameError() {
            return _.has(this.errors, 'lastname') && this.errors.lastname !== null;
        },
        withFirstnameError() {
            return _.has(this.errors, 'firstname') && this.errors.firstname !== null;
        },
        withBirthdayError() {
            return _.has(this.errors, 'birthday') && this.errors.birthday !== null;
        },
        withClubError() {
            return _.has(this.errors, 'club_id') && this.errors.club_id !== null;
        },
        lastnameLabel() {
            return this.withLastnameError ? trans(this.errors.lastname[0]) : trans('app.common.lastname');
        },
        firstnameLabel() {
            return this.withFirstnameError ? trans(this.errors.firstname[0]) : trans('app.common.name');
        },
        birthdayLabel() {
            return this.withBirthdayError ? trans(this.errors.birthday[0]) : trans('app.common.birthday');
        },
        clubLabel() {
            return this.withClubError ? trans(this.errors.club_id[0]) : trans('app.club.name');
        },
    },
    methods: {
        init() {
            this.getPerson();
            this.getClubs();
        },
        selectedClub(clubId) {
            if (clubId === 0) {
                return this.person.club_id === null;
            }
            return this.person.club_id === clubId;
        },
        updatePerson() {
            axios.put('/api/frontend/person/' + this.$route.params.id, this.person)
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
        getPerson() {
            axios.get('/api/frontend/person/' + this.$route.params.id)
                .then(response => {
                    this.person = response.data.data;
                })
                .catch();
        },
        getClubs() {
            axios.get('/api/frontend/club')
                .then(response => {
                    this.clubs = response.data.data;
                })
                .catch();
        },
    }
};
</script>
