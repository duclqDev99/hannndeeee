export class Helpers {
    static jsonDecode(jsonString, defaultValue) {
        if (typeof jsonString === 'string') {
            let result
            try {
                result = $.parseJSON(jsonString)
            } catch (err) {
                result = defaultValue
            }
            return result
        }
        return null
    }
}

;(($) => {
    let $body = $('body')

    $body.on('click', 'form.import-material button.import-material', (event) => {
        event.preventDefault()
        event.stopPropagation()
        let $form = $(event.currentTarget).closest('form')
        $form.find('input[type=file]').val('').trigger('click')
    })

    $body.on('change', 'form.import-material input[type=file]', (event) => {
        let $form = $(event.currentTarget).closest('form')
        let file = event.currentTarget.files[0]
        var filename = file.name;
        var extension = filename.substring(filename.lastIndexOf(".")).toUpperCase();
        if (extension == '.XLS' || extension == '.XLSX') {
            let arrayData = excelFileToJSON(file);
            const reader = new FileReader();
            reader.readAsBinaryString(file);
            reader.onload = function(e) {
                    var data = e.target.result;
                    var workbook = XLSX.read(data, {
                        type : 'binary'
                    });
                    var result = {};
                    workbook.SheetNames.forEach(function(sheetName) {
                        var roa = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName]);
                        if (roa.length > 0) {
                            result[sheetName] = roa;
                        }
                    });
                    let arrayData = result.Worksheet
                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: {
                            json_data: arrayData,
                        },
                        dataType: 'json',
                        beforeSend: () => {
                            Botble.blockUI()
                        },
                        success: (res) => {
                            Botble.showNotice(res.error ? 'error' : 'success', res.messages)
                            if (!res.error) {
                                const tableId = $form.find('table').prop('id')
                                if (window.LaravelDataTables[tableId]) {
                                    window.LaravelDataTables[tableId].draw()
                                }
                            }
                            Botble.unblockUI()
                        },
                        complete: () => {
                            Botble.unblockUI()
                        },
                        error: (res) => {
                            Botble.showError(res.message ? res.message : 'Some error occurred')
                        },
                    })
                }




        }else{
            alert("Please select a valid excel file.");
        }

    })
})(jQuery)
function excelFileToJSON(file){
    try {

      }catch(e){
          console.error(e);
      }
}
