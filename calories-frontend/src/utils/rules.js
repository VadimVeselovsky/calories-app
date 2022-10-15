export default {
    required: value => !!value || 'Required',
    min0: value => Number(value) >= 0 || 'Can\'t be negative',
    max: max => value => Number(value) <= max || `Can't be more than ${max}`,
    lessThan: (length) => value => value.length <= length || `Can't be more than ${length} characters`,
}