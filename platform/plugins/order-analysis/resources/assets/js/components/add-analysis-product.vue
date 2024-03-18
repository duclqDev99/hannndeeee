<template>
  <div class="ui vertical segment">
    <div class="flexbox">
      <div class="flex-content">
        <div class="button-group" style="margin-bottom: 5px">
          <button type="button" @click="reset" class="btn btn-secondary" style="background-color:#46C2CB; color: #fff; margin-right:5px">Xóa tất cả</button>
        </div>
        <div>
          <multi-list-select
            :list="dataList"
            option-value="id"
            option-text="name"
            :custom-text="codeAndNameAndDesc"
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
            <th>id</th>
            <th>Tên</th>
            <th>Mã</th>
            <th>Số lượng</th>
            <th>Đơn vị</th>
          </tr>
          </thead>
          <tbody v-for="item in items" :key="item.code">

          <tr>
            <td>{{item.id}}</td>
            <td>{{item.name}}</td>
            <td>{{item.code}}</td>
            <td>
                <input
                    :name="`quantityAndId[${item.id}]`"
                    type="number"
                    style="width: 200px"
                    class="form-control"
                    v-model="item.quantity"
                    @input="updateTotal(item)"
                    placeholder="Nhập số lượng"
                    min = 1
                />
            </td>
            <td>{{item.unit}}</td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import unionWith from 'lodash/unionWith'
import isEqual from 'lodash/isEqual'
import { MultiListSelect } from "vue-search-select"
import 'vue-search-select/dist/VueSearchSelect.css';

export default {
    props: {
         data: {
            type: Object,
            default: () => [],
        },
    },
  data () {
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
    codeAndNameAndDesc (item) {
      return `${item.code}`
    },
    onSelect (selectedItems) {
        this.items = selectedItems.map(newItem => {
            const existingItem = this.items.find(item => item.id === newItem.id);

            if (existingItem) {
                return { ...newItem, quantity: existingItem.quantity };
            } else {
                return { ...newItem, quantity: 1 };
            }
        });
    },
    reset () {
      this.items = []
    },
    updateTotal(item) {
        console.log(item);
    },
    getValueFromVueComponent() {
        return this.items;
    }
},
    mounted() {
        console.log(this.data.value);
        this.items = this.data.value
    },
  components: {
    MultiListSelect
  }
}
</script>
