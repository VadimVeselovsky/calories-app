<template>
  <v-container class="pb-16">
    <v-row justify="center">
      <v-col cols="12" md="8" xl="4">
        <div class="d-flex justify-space-between align-center">
          <div class="pl-5 text-center d-flex flex-wrap">
            <span class="text-h6 font-weight-regular mr-2"> Food for</span>
            <div class="text-h5">
              {{
                dateRange.length === 1
                  ? dateRange[0]
                  : dateRange[0] + " - " + dateRange[1]
              }}
            </div>
          </div>
          <div><DatePickerButton :callback="onDateChange" /></div>
        </div>
        <v-progress-circular
          class="mx-auto d-block py-16"
          v-if="isLoading"
          indeterminate
          color="primary"
        ></v-progress-circular>
        <template v-else>
          <div
            v-if="mealsByYears.length === 0"
            class="text-h6 font-weight-regular mt-12 mb-8 text-center"
          >
            No food entries for selected dates
          </div>
          <div class="mt-16" v-for="year in mealsByYears" :key="year.year">
            <div
              class="text-h4 font-weight-regular pl-5"
              v-if="mealsByYears.length >= 2"
            >
              {{ year.year }}
            </div>
            <template v-for="month in year.months">
              <div
                :key="month.month + ' month'"
                class="d-flex text-h4 font-weight-regular mt-10 pl-5"
              >
                <div>
                  {{ month.month }}
                </div>
                <v-tooltip bottom v-if="month.isOverspent">
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon
                      v-bind="attrs"
                      v-on="on"
                      large
                      color="green"
                      class="ml-3"
                      >mdi-cash</v-icon
                    >
                  </template>
                  <span
                    >You've spent more than
                    {{ $store.getters.maximums.money }} dollars this month</span
                  >
                </v-tooltip>
              </div>
              <template v-for="day in month.days">
                <div
                  :key="day.day + ' day'"
                  class="text-h6 font-weight-regular mt-6 pl-5 d-flex"
                >
                  <div>{{ month.month }} {{ day.day }}</div>
                  <v-tooltip bottom v-if="day.isTooMuchCalories">
                    <template v-slot:activator="{ on, attrs }">
                      <v-icon
                        v-bind="attrs"
                        v-on="on"
                        large
                        color="orange"
                        class="ml-1"
                        >mdi-lightning-bolt</v-icon
                      >
                    </template>
                    <span
                      >You have consumed more than
                      {{ $store.getters.maximums.calories }} calories this
                      day</span
                    >
                  </v-tooltip>
                </div>
                <v-card
                  :key="meal.id + ' meal'"
                  v-for="meal in day.meals"
                  class="px-5 py-3 mt-2"
                >
                  <div class="d-flex">
                    <div class="flex-grow-1">
                      <div class="d-flex">
                        <div class="mr-3">
                          {{ meal.time_eaten.split(" ")[1].slice(0, 5) }}
                        </div>
                        <div>
                          {{ meal.name }}
                        </div>
                      </div>
                      <div class="d-flex mt-4">
                        <v-col lg="4" class="d-flex align-center pa-0">
                          <v-icon
                            :color="day.isTooMuchCalories ? 'orange' : null"
                            class="mr-2"
                            >mdi-lightning-bolt</v-icon
                          >
                          {{ Number(meal.calories).toFixed(0) * 1 }} cal.
                        </v-col>
                        <v-col lg="4" class="d-flex align-center ml-7 pa-0">
                          <v-icon
                            :color="month.isOverspent ? 'green' : null"
                            class="mr-2"
                            >mdi-cash</v-icon
                          >
                          {{ Number(meal.price).toFixed(2) * 1 }} $
                        </v-col>
                      </div>
                    </div>
                    <div
                      class="flex-column flex-sm-row d-flex align-center ml-4"
                    >
                      <v-btn
                        :to="{
                          name: 'TrackerUpdateMeal',
                          params: { id: meal.id },
                        }"
                        fab
                        small
                        elevation="0"
                      >
                        <v-icon small>mdi-pencil</v-icon>
                      </v-btn>
                      <v-btn
                        @click="deleteMeal(meal.id)"
                        :disabled="isLoading"
                        fab
                        small
                        class="ml-0 ml-sm-3 mt-2 mt-sm-0"
                        elevation="0"
                      >
                        <v-icon small>mdi-delete</v-icon>
                      </v-btn>
                    </div>
                  </div>
                </v-card>
              </template>
            </template>
          </div>
        </template>
      </v-col>
    </v-row>
    <v-row justify="center" v-show="!isLoading">
      <v-btn :to="{ name: 'TrackerCreateMeal' }" class="mt-4" fab elevation="0">
        <v-icon>mdi-plus</v-icon>
      </v-btn>
    </v-row>
  </v-container>
</template>

<script>
import DatePickerButton from "@/components/Index/DatePickerButton";
import dates from "@/utils/dates";
import axios from "@/utils/axios";
import consts from "@/utils/consts";
import { debounce, isEqual } from "lodash-es";

export default {
  components: { DatePickerButton },

  data() {
    return {
      dateRange: [dates.today()],
      isLoading: true,
      mealsByYears: [],
      statistics: null,
      haveLoaded: false,
    };
  },

  mounted() {
    this.loadLS();
    this.pullItems();
  },

  beforeDestroy() {
    this.saveLS();
  },

  computed: {
    pullItemsDebounced() {
      return debounce(() => this.pullItems(), 1500);
    },

    LSName() {
      return "calories-app.index";
    },
  },

  methods: {
    loadLS() {
      const saved = localStorage.getItem(this.LSName + ".date-range");
      if (saved) {
        this.dateRange = JSON.parse(saved);
      }
    },
    saveLS() {
      localStorage.setItem(
        this.LSName + ".date-range",
        JSON.stringify(this.dateRange)
      );
    },
    deleteMeal(id) {
      this.isLoading = true;
      axios.delete("meals/" + id).then(this.pullItems);
    },

    pullItems() {
      this.isLoading = true;
      axios
        .get("meals", {
          params: {
            from: this.dateRange[0] + " 00:00:00",
            to:
              (this.dateRange.length === 1
                ? this.dateRange[0]
                : this.dateRange[1]) + " 23:59:59",
          },
        })
        .then(this.mealsRequestHandler)
        .finally(() => (this.isLoading = false));
    },

    mealsRequestHandler({ data }) {
      this.haveLoaded = true;

      const meals = data.items.data,
        years = {},
        sortEntries = (a, b) => a[0] - b[0],
        months = consts.MONTHS;

      meals.forEach((meal) => {
        const datetime = meal.time_eaten.split("-"),
          year = datetime[0],
          month = datetime[1],
          day = datetime[2].split(" ")[0];

        years[year] = years[year] || {};
        years[year][month] = years[year][month] || {};
        years[year][month][day] = years[year][month][day] || [];
        years[year][month][day].push(meal);
      });

      this.mealsByYears = Object.entries(years)
        .sort(sortEntries)
        .map((yearPair) => ({
          year: yearPair[0],
          months: Object.entries(yearPair[1])
            .sort(sortEntries)
            .map((pair) => ({
              month: months[pair[0] - 1],
              isOverspent: data.statistics.overspentMonths.includes(
                `${yearPair[0]}-${pair[0]}`
              ),
              days: Object.entries(pair[1])
                .sort(sortEntries)
                .map((pair) => ({
                  day: pair[0] * 1,
                  meals: pair[1],
                  isTooMuchCalories:
                    pair[1].reduce(
                      (acc, meal) => acc + Number(meal.calories),
                      0
                    ) > this.$store.getters.maximums.calories,
                })),
            })),
        }));
    },

    onDateChange(dateRange) {
      this.dateRange = dateRange;
      if (
        this.dateRange.length === 2 &&
        this.dateRange[0] > this.dateRange[1]
      ) {
        this.dateRange.reverse();
      }
      this.pullItemsDebounced();
    },
  },
};
</script>