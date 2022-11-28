let select = document.querySelector('#language-select');
var per_num = 10;

var session = getSession();

const RunDialog = document.querySelector('#RunDialog');
const closedialog=document.querySelector("#close_id_btn");
const cancel_id_btn=document.querySelector("#cancel_id_btn");
const Rundialog=document.querySelector("#run_id_btn");
const background_grey = document.querySelector('#background_grey') 
function showDialog() {
    
    RunDialog.classList.remove("notVisible");
    background_grey.classList.remove('notVisible');

    closedialog.addEventListener("click",()=>{
        RunDialog.classList.add("notVisible");
        background_grey.classList.add("notVisible");
    })
    cancel_id_btn.addEventListener("click",()=>{
        RunDialog.classList.add("notVisible");
        background_grey.classList.add("notVisible");
        
    })
    Rundialog.addEventListener("click",()=>{
        RunDialog.classList.add("notVisible");
        background_grey.classList.add("notVisible");
        let url = 'https://mybinder.org/v2/gh/Sarus-Support/Python-Samples/HEAD';
        window.open(url, '_blank').focus();
    })
}
$.ajax({
    url: 'analytic-demo/python-list.json',
    type: 'GET',
    error: function (XMLHttpRequest, textStatus, errorThrown) {
        // functionNotificationMessage({
        //     text: 'There is an error at ' + this.url,
        //     type: 'error'
        // });
    },
    success: function () {
        let pythonOption = `<option  selected value="python">Python</option>`;
        $('#language-select').append(pythonOption);
        $.ajax({
            url: 'analytic-demo/r-list.json',
            type: 'GET',
            error: function (XMLHttpRequest, textStatus, errorThrown) {
            },
            success: function () {
                let rOption = `<option value="r">R</option>`;
                $('#language-select').append(rOption);
            }
        });
    }
});

// If the r-list.json file axist then show the select option



setTimeout(() => {
    //  show_grid($('#language-select').find("option:first-child").val(), per_num);
    show_grid($('#language-select').find("option:first-child").val(), per_num);
    accbtn();
},
300);


async function show_grid(selection, per_num = 2, sel_page = 1) {
    var data;
    if (selection == "python") {
        data = await $.ajax({
            type: "GET",
            url: "analytic-demo/python-list.json",
            dataType: "JSON",
            success: function (data) {

            },
            error: function (xhr) {
                functionNotificationMessage({
                    text: "The report list file for the python demos cannot be found.",
                    type: 'error'
                });
            }
        });
    } else {
        data = await $.ajax({
            type: "GET",
            url: "analytic-demo/r-list.json",
            dataType: "JSON",
            success: function (data) {

            },
            error: function (xhr) {
                functionNotificationMessage({
                    text: "The report list file for the R demos cannot be found.",
                    type: 'error'
                });

            }
        });
    }

    var contents = $('<div>');
    var start = (sel_page - 1) * per_num;
    var end = (start + per_num) < data.length ? (start + per_num) : data.length;

    for (var i = start; i < end; i++) {
        var item = $('<div class="accordion-file">');
        
            // var lock_img = (data[i].free_enabled || (!data[i].free_enabled && (session !== null && session !== "" && session !== undefined))) ? "unlocked.png" : "locked.png";

            // var btn_active = ((data[i].free_enabled && data[i].filename != "") || (!data[i].free_enabled && (session !== null && session !== "" && session !== undefined) && data[i].filename != "")) ? "" : "disabled";
            
            // var btn_session;
            // if (session !== null && session !== "" && session !== undefined) {
            //     btn_session = '';
            // } else {
            //     btn_session = 'disabled';
            // }
        var lock_img = data[i].FreeView_enabled  ? "locked.png" : "unlocked.png";
        var btn_active = data[i].FreeView_enabled ? "disabled" : '';
        var btn_session = data[i].FreeDownload_enabled ? "disabled": "";
        var btn_run = data[i].FreeRun_enabled ? "disabled": "";
        $('<button class="accordion-btn">' + data[i].report_name + ' <img src="resources/images/' + lock_img + '" alt=""></button>').appendTo(item);

        var item_contents = $('<div class="accordion-content">');

        $('<p>' + data[i].description + '</p>').appendTo(item_contents);

        var message = "";
        var filename = "";
        // var filecontents = "";
        if (btn_active == "") {
            try {
                await $.ajax({
                    type: "GET",
                    url: data[i].filename,
                    dataType: "text",
                    success: function (data) {
                        btn_active = "";
                        // filecontents = data;
                    },
                    error: function (xhr) {}
                });
            } catch (e) {
                btn_active = "disabled";
                btn_session = 'disabled'; 
                filename = data[i].filename.split("/");
                filename = filename[filename.length - 1]
                message = "The source file '" + filename + "' was not found"
            }
        } else {
            if (data[i].filename == "") {
                if (selection == "python") {
                    message = "The  Python/Jupyter samples report list was not found!";
                } else if (selection == "r") {
                    message = "The R samples report list was not found!";
                } else {
                    message = "The Python samples report list was not found!";
                }
            }
        }
        
        filename = data[i].filename.split("/");
        filename = filename[filename.length - 1];
        $('<div class="btn-wrapper"><button class="btn ' + btn_active + ' btn-primary me-4" onclick="javascript:code_view(\'' + selection + '\', \'' + data[i].filename + '\', \'' + btn_active + '\');">View</button> <button class="btn ' + btn_run + ' btn-success me-4" onclick="javascript:showDialog();">Run</button><button class="btn ' + btn_session + '  btn-success me-4" onclick="javascript:download(\'' + data[i].filename + '\', \'' + btn_session + '\');" >Download</button><span style="margin:6px 0 0 30px; color:#aaa">' + message + '</span></div>').appendTo(item_contents);

        item_contents.appendTo(item);
        item.appendTo(contents);
    }


    if (selection == "python") {
         $('#python-body').html(contents); 
    } else if (selection == "r") {
        $('#r-body').html(contents); 
    } else {
        $('#jupyter-body').html(contents);
        
    }

    accbtn();
    pagination(selection, per_num, sel_page, data.length);
}

//When select option changes. it Means when user select other options
select.addEventListener("change", function () {
    let pyfiles = document.querySelector('.python-files');
    rfiles = document.querySelector('.r-files');
    accordion = document.querySelector('.accordion-collapse')

    // When python is selected then hide jupyter files and R files
    if (select.value == 'python') {
        pyfiles.style.display = 'block';
        rfiles.style.display = 'none';
        show_grid("python", per_num);
    }

    // When R is selected then hide jupyter files and Python files. Show R files
    else if (select.value == 'r') {
        rfiles.style.display = 'block';
        pyfiles.style.display = 'none';
        show_grid("r", per_num);
    }

});
function RunReport (fileName) {
    let url = 'https://hub.gke2.mybinder.org/user/sarus-support-python-samples-fhrsgy3b/lab/workspaces/auto-X/tree/bu/' + fileName;
    window.open(url, '_blank').focus();
}
function RundirectReport() {
    let url = 'https://mybinder.org/v2/gh/Sarus-Support/Python-Samples/HEAD';
    window.open(url, '_blank').focus();
}
//this is the accordion button
function accbtn() {
    let accbtn = document.getElementsByClassName("accordion-btn");
    for (let i = 0; i < accbtn.length; i++) {
        //when one of the buttons are clicked run this function
        accbtn[i].onclick = function () {
            //letiables
            let panel = this.nextElementSibling;
            let accContent = document.getElementsByClassName("accordion-content");
            let accActive = document.getElementsByClassName("accordion-btn active");

            /*if pannel is already open - minimize*/
            if (panel.style.maxHeight) {
                //minifies current pannel if already open
                panel.style.maxHeight = null;
                //removes the 'active' class as toggle didnt work on browsers minus chrome
                this.classList.remove("active");
            } else { //pannel isnt open...
                //goes through the buttons and removes the 'active' css (+ and -)
                for (let ii = 0; ii < accActive.length; ii++) {
                    accActive[ii].classList.remove("active");
                }
                //Goes through and removes 'activ' from the css, also minifies any 'panels' that might be open
                for (let iii = 0; iii < accContent.length; iii++) {
                    this.classList.remove("active");
                    accContent[iii].style.maxHeight = null;
                }
                //opens the specified pannel
                panel.style.maxHeight = panel.scrollHeight + "px";
                //adds the 'active' addition to the css.
                this.classList.add("active");
            }
        } //closing to the acc onclick function
    } //closing to the for loop.
}


function pagination(selection, per_num = 3, sel_page = 1, dataRows) {
    total_page = Math.ceil(dataRows / per_num);
    var pagi = $('<ul class="pagination">');
    var prev = (sel_page == 1) ? "disabled" : "";
    $('<li class="page-item ' + prev + '"><a class="page-link" href="javascript:show_grid(\'' + selection + '\', ' + per_num + ', ' + (sel_page - 1) + ')">Prev</a></li>').appendTo(pagi);
    for (var i = 1; i <= total_page; i++) {
        if (i == sel_page)
            $('<li class="page-item active"><a class="page-link">' + i + '</a></li>').appendTo(pagi);
        else
            $('<li class="page-item"><a class="page-link" href="javascript:show_grid(\'' + selection + '\', ' + per_num + ', ' + i + ')">' + i + '</a></li>').appendTo(pagi);
    }
    var next = (sel_page == total_page) ? "disabled" : "";
    $('<li class="page-item ' + next + '"><a class="page-link" href="javascript:show_grid(\'' + selection + '\', ' + per_num + ', ' + (sel_page + 1) + ')">Next</a></li>').appendTo(pagi);

    if (selection == "python") {
        $('#python_pagination').html(pagi);
    } else if (selection == "r") {
        $('#r_pagination').html(pagi);
    } else {
        $('#jupyter_pagination').html(pagi);
    }
}

function code_view(selection, url, btn_active) {
    if (btn_active != 'disabled') {
       if (selection == "python") {
            msg = "View  the  source file in a Jupyter Book reader?";
            dialogWindow(msg, 'query', 'confirm', 'Report Viewer',
                function () {
                    window.open("code_viewer?filetype=" + "jupyter" + "&url=" + url, '_blank');
                }, null, null, {
                    Ok: 'Yes',
                    Cancel: 'No'
                }
            )
        } else {
            msg = "View  the  source file in a web page?";
            // msg = "<input type='radio'/>asd<input type='radio'/>asd";
            dialogWindow(msg, 'query', 'confirm', 'Report Viewer',
                function () {
                    window.open("code_viewer?filetype=" + selection + "&url=" + url, '_blank');
                }, null, null, {
                    Ok: 'Yes',
                    Cancel: 'No'
                }
            )
        }
    }
}

function download(url, btn_active) {
    // msg = "View  the  source file in a web page?";
    // dialogWindow(msg, 'query', 'confirm', null,
    //     function () {
    if (btn_active != 'disabled') {
        filename = url.split("/");
        filename = filename[filename.length - 1]
        axios({
                url: url,
                method: 'GET',
                responseType: 'blob'
            })
            .then((response) => {
                const url = window.URL
                    .createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            })
    }
    //     }, null, null, { Ok: 'Yes', Cancel: 'No' }
    // )


}