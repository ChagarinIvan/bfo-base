<template>
    <div class="row mb-3" v-if="this.isAuth">
        <div class="col-12">
            <ui-button :color="'success'"
                       :icon="'bi-file-earmark-plus-fill'"
                       :url="'/persons/create'"
                       :text="'app.person.create_button'"
            ></ui-button>
        </div>
    </div>
    <div class="row">
        <div class="bootstrap-table bootstrap5">
            <div class="fixed-table-toolbar">
                <div class="float-right search btn-group">
                    <input class="form-control search-input"
                           type="search"
                           v-model="this.search"
                           :placeholder="translate('app.common.search')">
                </div>
            </div>
            <table class="table table-bordered">
                <thead class="table-dark">
                <tr>
                    <th @click="sort('fio')">{{ $t('app.common.fio') }} <i class="fas float-end" :class="sortColumn('fio')"></i></th>
                    <th>{{ $t('app.common.rank') }}</th>
                    <th @click="sort('events_count')">{{ $t('app.common.events_count') }} <i class="fas float-end" :class="sortColumn('events_count')"></i></th>
                    <th @click="sort('club_name')">{{ $t('app.club.name') }} <i class="fas float-end" :class="sortColumn('club_name')"></i></th>
                    <th @click="sort('birthday')">{{ $t('app.common.birthday_year') }} <i class="fas float-end" :class="sortColumn('birthday')"></i></th>
                    <th v-if="isAuth"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="person in this.persons">
                    <td><a :href="'/persons/' + person.id + '/show'"><span v-html="markedText(person.lastname + ' ' + person.firstname)"></span></a></td>
                    <td><a :href="'/ranks/person/' + person.id"><span v-html="markedText(person.rank)"></span></a></td>
                    <td><span v-html="markedText(person.events_count)"></span></td>
                    <td v-if="person.club_id > 0"><a :href="'/club/' + person.club_id + '/show'"><span v-html="markedText(person.club_name)"></span></a></td>
                    <td v-else><span v-html="markedText(person.club_name)"></span></td>
                    <td><span v-html="markedText(year(person.birthday))"></span></td>
                    <td v-if="isAuth">
                        <ui-button :url="'/persons/' + person.id + '/edit'"></ui-button>
                        <ui-button :url="'/persons/' + person.id + '/delete'"
                                   :color="'danger'"
                                   :icon="'bi-trash-fill'"
                                   :text="'app.common.delete'"
                        ></ui-button>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="fixed-table-pagination">
                <div class="float-left pagination-detail">
                    <span class="pagination-info">{{ $t('app.table.items') }} {{ this.firstItem }} {{ $t('app.table.po') }} {{ this.lastItem }} {{ $t('app.table.iz') }} {{ this.maxCount }}</span>
                    <div class="page-list">
                        <div class="btn-group dropdown dropup">
                            <button class="btn btn-secondary dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown"
                                    data-form-type=""
                                    data-dashlane-label="true">
                                <span class="page-size">{{ this.perPageLine }}</span>
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" @click="setPerPage(10)" :class="{ active: activePerPage(10) }">10</a>
                                <a class="dropdown-item" @click="setPerPage(25)" :class="{ active: activePerPage(25) }">25</a>
                                <a class="dropdown-item" @click="setPerPage(50)" :class="{ active: activePerPage(50) }">50</a>
                                <a class="dropdown-item" @click="setPerPage(100)"
                                   :class="{ active: activePerPage(100) }">100</a>
                                <a class="dropdown-item" @click="setPerPage(this.maxCount)"
                                   :class="{ active: activePerPage(this.maxCount) }">{{ $t('app.all') }}</a></div>
                        </div>
                        Запісаў на старонку
                    </div>
                </div>
                <div class="float-right pagination">
                    <ul class="pagination">
                        <li class="page-item page-pre" v-if="this.page > 1">
                            <a class="page-link" @click="this.pagination(-1)">{{ $t('app.pagination.previous') }}</a>
                        </li>
                        <li class="page-item" v-if="this.page > 3">
                            <a class="page-link" @click="this.pagination(1 - this.page)">1</a>
                        </li>
                        <li class="page-item page-last-separator disabled" v-if="this.page > 3">
                            <a class="page-link">...</a>
                        </li>
                        <li class="page-item" v-if="this.page > 2">
                            <a class="page-link" @click="this.pagination(-2)">{{ this.page - 2 }}</a>
                        </li>
                        <li class="page-item" v-if="this.page > 1">
                            <a class="page-link" @click="this.pagination(-1)">{{ this.page - 1 }}</a>
                        </li>
                        <li class="page-item active">
                            <a class="page-link">{{ this.page }}</a>
                        </li>
                        <li class="page-item" v-if="this.page + 1 <= this.lastPage">
                            <a class="page-link" @click="this.pagination(1)">{{ this.page + 1 }}</a>
                        </li>
                        <li class="page-item" v-if="this.page + 2 <= this.lastPage">
                            <a class="page-link" @click="this.pagination(2)">{{ this.page + 2 }}</a>
                        </li>
                        <li class="page-item page-last-separator disabled" v-if="this.page + 3 <= this.lastPage">
                            <a class="page-link">...</a>
                        </li>
                        <li class="page-item" v-if="this.page + 3 <= this.lastPage">
                            <a class="page-link" @click="this.pagination(this.lastPage - this.page)">{{ this.lastPage }}</a>
                        </li>
                        <li class="page-item page-next" v-if="this.page + 2 <= this.lastPage">
                            <a class="page-link" @click="this.pagination(1)">{{ $t('app.pagination.next') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import UiButton from "../UiButton.vue";
import { trans } from 'laravel-vue-i18n'

export default {
    components: {
        UiButton,
    },
    props: {
        auth: Boolean,
    },
    data() {
        return {
            isAuth: this.auth,
            persons: [],
            maxCount: 0,
            perPage: 10,
            page: 1,
            sortBy: 'fio',
            search: '',
            sortMode: 0, // 0 = ASC, 1 = DESC
        }
    },
    watch: {
        search: function() {
            this.getPersons();
        },
    },
    computed: {
        firstItem() {
            return (this.page - 1) * this.perPage + 1;
        },
        lastItem() {
            return this.page * this.perPage;
        },
        personsUrl() {
            return '/api/person?per_page=' + this.perPage
                + '&page=' + this.page
                + '&sort_by=' + this.sortBy
                + '&sort_mode=' + this.sortMode
                + '&search=' + this.search;
        },
        lastPage() {
            let number = this.maxCount / this.perPage;
            let page = parseInt(number + '');
            return number > page ? page + 1 : page;
        },
        perPageLine() {
            return this.perPage === this.maxCount ? this.translate('app.all') : this.perPage;
        }
    },
    mounted() {
        this.getPersons();
    },
    methods: {
        year(date) {
            return date === null ? '' : new Date(date).getFullYear();
        },
        activePerPage(perPage) {
            return this.perPage === perPage;
        },
        translate(key) {
            return trans(key);
        },
        getPersons() {
            axios.get(this.personsUrl)
                .then((response) => {
                    this.persons = response.data.data;
                    this.maxCount = response.data.meta.total;

                    if (this.maxCount === 0) {
                        this.page = 1;
                    } else if(this.maxCount < this.firstItem) {
                        this.page = this.lastPage;
                    }
                })
                .catch();
        },
        pagination(pages) {
            this.page = this.page + pages;
            this.getPersons();
        },
        setPerPage(perPage) {
            this.perPage = perPage;
            this.getPersons();
        },
        sort(sortBy) {
            this.sortMode = sortBy === this.sortBy ? (this.sortMode === 1 ? 0 : 1) : 0;
            this.sortBy = sortBy;
            this.getPersons();
        },
        sortColumn(column) {
            return this.sortBy === column ? (this.sortMode === 1 ? 'fa-sort-down' : 'fa-sort-up') : 'fa-sort';
        },
        markedText(text) {
            if (
                this.search !== ''
                && text !== null
                && text !== ''
                && (text + '').toLowerCase().includes(this.search.toLowerCase())
            ) {
                return (text + '').replaceAll(this.search, '<mark>' + this.search + '</mark>')
                    .replaceAll(this.search.toLowerCase(), '<mark>' + this.search.toLowerCase() + '</mark>');
            }

            return text;
        },
    }
};
</script>
