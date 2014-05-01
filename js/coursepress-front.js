function disenroll_confirmed() {
    return confirm(student.disenroll_alert);
}

function disenroll() {
    if (disenroll_confirmed()) {
        return true;
    } else {
        return false;
    }
}