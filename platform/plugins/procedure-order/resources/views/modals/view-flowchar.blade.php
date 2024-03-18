

<style>
    .anavbar {
        position: fixed;
        bottom: 0;
        width: 100%;
        margin-bottom: 10px;
        border: 1px solid #e7e7e7;
        background-color: #f3f3f3;
        opacity: 0.1;
        cursor: pointer;
        z-index: 100
    }

        .anavbar:hover {
            opacity: 1.0;
        }

        .anavbar ul li a {
            display: block;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            float: left;
        }

        .anavbar ul li a:hover {
            background-color: #ccc9c9;
        }

    ul.horizontal {
        list-style-type: none;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
</style>
<div class="modal fade" id="modal-view-flowchart" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Sơ đồ</h5>
          <button type="button" " class="btn-close close-modal-view-flowchart" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="anavbar" class="anavbar" style="display:none">
                <ul class="horizontal">
                    <li><a id="pre">Previous</a></li>
                    <li><a id="next">Next</a> </li>
                    <li><a id="show">Show All</a> </li>
                    <li><a id="hide">Hide All</a> </li>
                    <li><a id="start">Start Animation</a> </li>
                    <li><a id="stop">Stop Animation</a> </li>
                </ul>
            </div>

            <div class="container">
                <div class="right">
                    <div id="desc"></div>
                </div>
                <div class="left">
                    <svg id="demo" width="400" height="700">

                    </svg>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button"  class="btn btn-secondary btn-trigger-export-qrcode-version close-modal-view-flowchart" data-bs-dismiss="modal">Close</button>
          {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    prepare_SVG("demo");
    var object1 = add_theObject(new Terminal(300, 50, 0.7, ["Start"], 20, "Search in an integer array"));
    var object2 = add_theObject(new Process(300, 150, 1, ["s_value= 5, pos: -1", "List={1, 3, 5, 7, 9}"], 10, "<b>Description can be entered here...</b>"));
    var object3 = add_theObject(new Preparation(300, 250, 1, ["i=0;i&lt;List.length-1;i++"], 10, "<i>Loop</i>"));
    var object4 = add_theObject(new Decision(300, 380, 1, ["s_value==List[i]"], 10));
    var object5 = add_theObject(new Process(150, 480, 0.8, ["pos=i"], 12, "<b>position value is changed</b>"));
    var object6 = add_theObject(new Display(300, 560, 0.8, ["Show Position"], 10));
    var object7 = add_theObject(new Terminal(300, 645, 0.7, ["End"], 20));

    var o_line1 = draw_theLine(new Line(object1, object2));
    var o_line2 = draw_theLine(new Line(object2, object3));
    var o_line3 = draw_theLine(new Line(object3, object4));
    var o_line4 = draw_theLine(new Line(object4, object3, null, null, "No", 12));
    var o_line5 = draw_theLine(new Line(object4, object5, -1, -1, "Yes", 12));
    //var o_line6 = draw_theLine(new Line(object5, object3, 0, 2, null, null, "Find last position"));
    var o_line6 = draw_theLine(new Line(object5, object6, 1, 0, null, null, "Find and Break"));
    var o_line7 = draw_theLine(new Line(object3, object6));
    var o_line8 = draw_theLine(new Line(object6, object7));

    var groups = [object1, [object2, o_line1], [object3, o_line2], [o_line3, object4], o_line4, [o_line5, object5], [o_line6, o_line7, object6], [object7, o_line8]];
    prepareClassforAnimation(groups);
    initializeAnimation(groups.length - 1);
</script>
