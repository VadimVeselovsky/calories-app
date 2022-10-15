<template>
  <v-container class="mt-4">
    <v-row justify="center">
      <v-col cols="8">
        <v-card>
          <v-card-title
            >Food Entries
            <v-btn
              class="ml-3"
              :to="{ name: 'AdminCreateMeal' }"
              fab
              small
              elevation="0"
            >
              <v-icon>mdi-plus</v-icon></v-btn
            ></v-card-title
          >
          <v-card-text>
            <v-data-table
              :headers="headers"
              :items="meals"
              :options.sync="options"
              :server-items-length="totalMeals"
              :loading="loading"
              item-key="name"
              class="elevation-1"
            >
              <template v-slot:top>
                <form class="mx-4" @submit.prevent="pullMeals">
                  <v-container>
                    <v-row>
                      <v-col cols="4"
                        ><v-text-field
                          label="Username"
                          v-model="model.username"
                        ></v-text-field
                      ></v-col>
                      <v-col>
                        <v-menu
                          ref="menu1"
                          v-model="showPicker1"
                          :close-on-content-click="false"
                          :nudge-right="40"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on }">
                            <v-text-field
                              :value="model.period.join(' - ')"
                              :rules="rules.period"
                              label="Time period"
                              readonly
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            range
                            v-if="showPicker1"
                            v-model="model.period"
                            full-width
                          ></v-date-picker> </v-menu
                      ></v-col>

                      <v-btn type="submit" class="mt-6" elevation="0"
                        >Search</v-btn
                      >
                    </v-row>
                  </v-container>
                </form>
              </template>
              <template #item.actions="{ item }">
                <v-btn
                  :to="{
                    name: 'AdminUpdateMeal',
                    params: { id: item.id },
                  }"
                  :disabled="loading"
                  fab
                  small
                  elevation="0"
                >
                  <v-icon small>mdi-pencil</v-icon>
                </v-btn>
                <v-btn
                  @click="deleteMeal(item.id)"
                  :disabled="loading"
                  fab
                  small
                  class="ml-0 ml-sm-3 mt-2 mt-sm-0"
                  elevation="0"
                >
                  <v-icon small>mdi-delete</v-icon>
                </v-btn>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card> </v-col
      ><v-col cols="4">
        <div class="text-center mt-8">Number of new entries</div>
        <v-container>
          <v-row class="text-center">
            <v-col cols="6">
              <div class="text-h3 font-weight-light mb-1">{{ statistics.addedThisWeek }} </div>
              Last 7 days
            </v-col>
            <v-col cols="6">
              <div class="text-h3 font-weight-light mb-1">{{ statistics.addedPrevWeek }}</div>
              Week ago</v-col
            >
          </v-row>
        </v-container>
        <div class="text-center mt-8">Cal./week average</div>
        <div class="text-h3 font-weight-light mb-1 text-center mt-2">{{ statistics.averageCalories }}</div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import dates from "@/utils/dates";
import axios from "@/utils/axios";

export default {
  data() {
    return {
      model: {
        period: [dates.dateBeforeDays(7), dates.today()],
      },
      showPicker1: false,
      meals: [],
      options: {},
      totalMeals: 1,
      loading: true,
      statistics: {

      }
    };
  },

  watch: {
    "options.page": "pullMeals",
    "options.itemsPerPage": "pullMeals",
    "model.period"(value) {
      if (value.length == 2 && value[0] > value[1]) this.model.period.reverse();
    },
  },

  computed: {
    rules() {
      return {
        period: [],
      };
    },

    headers() {
      return [
        {
          sortable: false,
          value: "user_name",
          text: "User Name",
        },
        {
          sortable: false,
          value: "user_id",
          text: "User ID",
        },
        {
          sortable: false,
          value: "name",
          text: "Name",
        },
        {
          sortable: false,
          value: "calories",
          text: "Calories",
        },
        {
          sortable: false,
          value: "price",
          text: "Price",
        },
        {
          sortable: false,
          value: "time_eaten",
          text: "Time of consumption",
        },
        {
          sortable: false,
          value: "actions",
        },
      ];
    },
  },

  methods: {
    deleteMeal(id) {
      this.loading = true;
      axios.delete("meals/" + id).then(this.pullMeals);
    },

    pullMeals() {
      this.loading = true;

      const { page, itemsPerPage } = this.options;

      axios
        .get("meals", {
          params: {
            username: this.model.username,
            from: this.model.period[0] + " 00:00:00",
            to: this.model.period[1] + " 23:59:59",
            page,
            perPage: itemsPerPage,
          },
        })
        .then(({ data }) => {
          this.meals = data.items.data;
          this.totalMeals = data.items.total;
          this.loading = false;
          this.statistics = data.statistics;
        });
    },
  },
};
</script>