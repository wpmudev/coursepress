function delete_instructor_confirmed() {
    return confirm(instructor.delete_instructor_alert);
}

function removeInstructor() {
    if (delete_instructor_confirmed()) {
        return true;
    } else {
        return false;
    }
}