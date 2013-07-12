function delete_student_confirmed() {
    return confirm(student.delete_student_alert);
}

function removeStudent() {
    if (delete_student_confirmed()) {
        return true;
    } else {
        return false;
    }
}