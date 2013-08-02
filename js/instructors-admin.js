function delete_instructors_confirmed() {
    return confirm(instructor.delete_instructors_alert);
}

function removeInstructors() {
    if (delete_instructors_confirmed()) {
        return true;
    } else {
        return false;
    }
}