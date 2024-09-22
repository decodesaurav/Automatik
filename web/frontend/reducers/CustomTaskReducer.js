export const customeTaskState = {
    resourceName: {
        singular: "task",
        plural: "tasks",
    },
    conditionsOptions: "price",
    conditions: [],
    adjustment: {},
    taskType: "price",
    scheduleData: {
        scheduled_at_date: "",
        schedule_at_time: "",
        revert_at_date: "",
        revert_at_time: "",
        only_schedule_one_time: false,
        reschedule_frequency: ""
    }
};

export const actionTypes = {
    HANDLE_TASK_CHANGE: "HANDLE_TASK_CHANGE",
    HANDLE_SCHEDULE_TIME_CHANGE: "HANDLE_SCHEDULE_TIME_CHANGE",
    HANDLE_ADJUSTMENT_CHANGE: "HANDLE_ADJUSTMENT_CHANGE",
    HANDLE_CONDITION_CHANGE: "HANDLE_CONDITION_CHANGE",
    REMOVE_CONDITION: "REMOVE_CONDITION",
 };

const CustomTaskReducer = (state, action) => {
    switch (action.type) {
        case actionTypes.HANDLE_TASK_CHANGE:
            return { ...state, taskType: action.payload };
        case actionTypes.HANDLE_SCHEDULE_TIME_CHANGE:
            return {
                ...state, scheduleData: { ...state.scheduleData, ...action.payload }
            }
        case actionTypes.HANDLE_ADJUSTMENT_CHANGE:
            return {
                ...state,
                adjustment: { ...state.adjustment, ...action.payload }
            };
        case actionTypes.HANDLE_CONDITION_CHANGE: {
                const existingConditionIndex = state.conditions.findIndex(
                    (condition) => condition.field === action.payload.field
                );
    
                const updatedConditions = [...state.conditions];
    
                if (existingConditionIndex !== -1) {
                    updatedConditions[existingConditionIndex] = {
                        ...updatedConditions[existingConditionIndex],
                        ...action.payload.data,
                    };
                } else {
                    updatedConditions.push({
                        field: action.payload.field,
                        ...action.payload.data,
                    });
                }
    
                return {
                    ...state,
                    conditions: updatedConditions,
                };
            }
    
        case actionTypes.REMOVE_CONDITION: {
                const updatedConditions = state.conditions.filter(
                    (condition) => condition.field !== action.payload.field
                );
                return {
                    ...state,
                    conditions: updatedConditions,
                };
            }
        default:
            return state;
    }
}

export default CustomTaskReducer;
