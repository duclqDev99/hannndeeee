<template>
  <div class="ui vertical segment">
    <div class="flexbox">
      <div class="flex-content">
        <div class="button-group" style="margin-bottom: 5px">
            <button type="button" @click="reset" class="btn btn-secondary"
                        style="background-color:#46C2CB; color: #fff; margin-right:5px" v-if="items.length > 0">
                        Xóa tất cả</button>
        </div>
        <div>
          <multi-list-select
            :list="dataList"
            option-value="id"
            option-text="name"
            :custom-text="nameHub"
            :selected-items="items"
            @select="onSelect"
          >
          </multi-list-select>
        </div>
      </div>
      <div class="flex-result">
        <table class="ui celled table">
          <thead>
          <tr>
            <th>Danh sách đại lý</th>

          </tr>
          </thead>
          <tbody v-for="item in items" :key="item.name">
          <tr>
            <td>{{item.name}}</td>
            <input type="hidden" name="agent_id[]" :value="item.id">
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { MultiListSelect } from "vue-search-select"
import 'vue-search-select/dist/VueSearchSelect.css';

export default {
    props: {
        data: {
            type: Object,
            default: () => [],
        },
    },
    data() {
        const dataAttribute = this.data ? this.data.choices : null;
        return {
            dataList: dataAttribute,
            items: [],
            totalAmount: 0,
            materialList: []
        }
    },

    watch: {

    },
    methods: {
        nameHub(item) {
            return `${item.name}`
        },
        onSelect(selectedItems) {
            this.items = selectedItems.map(newItem => {
                const existingItem = this.items.find(item => item.id === newItem.id);

                if (existingItem) {
                    return { ...newItem, quantity: existingItem.quantity };
                } else {
                    return { ...newItem, quantity: 1 };
                }
            });
        },
        reset() {
            this.items = []
        },

        getValueFromVueComponent() {
            return this.items;
        }
    },
    mounted() {
        this.items = this.data.value
    },
    components: {
        MultiListSelect
    }
}
</script>
