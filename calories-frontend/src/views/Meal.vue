<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="4">
        <v-btn
          small
          text
          :to="{ name: isAdmin ? 'Admin' : 'Tracker' }"
          exact
          class="mt-4"
          fab
          elevation="0"
        >
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>
        <div class="mt-4 text-h4">{{ id ? "Edit" : "Create" }} Food Entry</div>
        <v-progress-circular
          class="mx-auto d-block py-16"
          v-if="isLoading"
          indeterminate
          color="primary"
        ></v-progress-circular>
        <v-form
          v-else
          class="mt-4"
          v-model="isValid"
          @submit.prevent="onSubmit"
          ref="form"
          ><v-text-field
            v-if="isAdmin"
            label="User id"
            v-model="model.user_id"
            :rules="rules.user_id"
            type="number"
          />
          <v-text-field
            label="Product name"
            v-model="model.name"
            :rules="rules.name"
          />
          <v-text-field
            min="0"
            label="Number of calories"
            suffix="cal."
            type="number"
            :rules="rules.calories"
            v-model="model.calories"
            class="mt-4"
          />

          <v-text-field
            min="0"
            label="Price"
            type="number"
            :rules="rules.price"
            v-model="model.price"
            class="mt-4"
          />
          <v-menu
            ref="menu2"
            v-model="showDatePicker"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="model.date"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-text-field
                :rules="rules.date"
                v-model="model.date"
                label="Date"
                prepend-icon="mdi-calendar-outline"
                readonly
                v-bind="attrs"
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker
              v-if="showDatePicker"
              v-model="model.date"
              full-width
              @click:date="$refs.menu2.save(model.date)"
            />
          </v-menu>
          <v-menu
            ref="menu"
            v-model="showTimePicker"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="model.time"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-text-field
                :rules="rules.time"
                v-model="model.time"
                label="Time"
                prepend-icon="mdi-clock-time-four-outline"
                readonly
                v-bind="attrs"
                v-on="on"
              ></v-text-field>
            </template>
            <v-time-picker
              v-if="showTimePicker"
              v-model="model.time"
              full-width
              @click:minute="$refs.menu.save(model.time)"
            />
          </v-menu>
          <div class="mt-4 d-flex justify-center">
            <v-btn type="submit">{{ id ? "Save" : "Add" }}</v-btn>
          </div>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import rules from "@/utils/rules";
import axios from "@/utils/axios";
import dates from "@/utils/dates";

export default {
  data() {
    return {
      model: {
        name: "",
        calories: "",
        price: "",
        time: `${String(new Date().getHours()).padStart(2, "0")}:${String(
          new Date().getMinutes()
        ).padStart(2, "0")}`,
        date: dates.today(),
      },
      showTimePicker: false,
      showDatePicker: false,
      isValid: false,
      isLoading: false,
      errors: {},
    };
  },

  computed: {
    isAdmin() {
      return this.$route.fullPath.startsWith("/admin");
    },

    rules() {
      return {
        name: [rules.required, rules.lessThan(150)],
        calories: [rules.required, rules.min0, rules.max(20000)],
        price: [rules.required, rules.min0],
        time: [rules.required],
        date: [rules.required],
        user_id: [rules.required],
      };
    },

    id() {
      return this.$route.params.id;
    },
  },

  mounted() {
    if (this.id) this.pullMeal();
  },

  methods: {
    pullMeal() {
      this.isLoading = true;
      axios
        .get("meals/" + this.id)
        .then(({ data }) => {
          this.model = {
            name: data.name,
            calories: data.calories,
            price: data.price,
            date: data.time_eaten.split(" ")[0],
            time: data.time_eaten.split(" ")[1].slice(0, 5),
          };
          if (this.isAdmin) {
            this.model.user_id = data.user_id;
          }
        })
        .then(() => (this.isLoading = false));
    },

    onSubmit() {
      if (!this.$refs.form.validate()) return;
      this.isLoading = true;

      axios[this.id ? "put" : "post"](this.id ? "meals/" + this.id : "meals", {
        name: this.model.name,
        calories: this.model.calories,
        price: this.model.price,
        time_eaten: this.model.date + " " + this.model.time + ":00",
        ...(this.isAdmin ? { user_id: this.model.user_id } : {}),
      })
        .then(() => {
          this.$router.push({ name: this.isAdmin ? "Admin" : "Tracker" });
        })
        .catch(({ response }) => {
          this.errors = response.data.errors;
        });
    },
  },
};
</script>
