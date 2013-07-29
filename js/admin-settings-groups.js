var ct = 1;

function new_link()
{
    addRow();
    jQuery('#r' + ct + 'td1').html('<input type="text" style="width:100%;" value="" name="item_title[]">');
    jQuery('#r' + ct + 'td4').html('<a href="javascript:removeElement(\'items\',\'r' + ct + '\');">' + group_settings.remove_string + '</a>');
}

function removeElement(parentDiv, childDiv) {
    if (childDiv == parentDiv) {
        //alert("The parent div cannot be removed.");
    }
    else if (document.getElementById(childDiv)) {
        var child = document.getElementById(childDiv);
        var parent = document.getElementById(parentDiv);
        parent.removeChild(child);
    }
    else {
        //alert("Child div has already been removed or does not exist.");
        //return false;
    }
}


function addRow() {
    ct++;
    var r = document.createElement('tr');
    r.setAttribute('id', 'r' + ct);

    var ca = document.createElement('td');
    ca.setAttribute('id', 'r' + ct + 'td1');

    var cd = document.createElement('td');
    cd.setAttribute('id', 'r' + ct + 'td4');

    var t = document.getElementById('items');

    r.appendChild(ca);
    r.appendChild(cd);
    t.appendChild(r);
}