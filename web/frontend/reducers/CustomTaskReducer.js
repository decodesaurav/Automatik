export const customeTaskState = {
    resourceName: {
        singular: "task",
        plural: "tasks",
    },
    conditionsOptions: ["price"],
    conditions: [],
    adjustment: {},
    taskName: "",
    taskType: "price",
    revertSchedule: false,
    scheduleOnlyOneTime: false,
    scheduleData: {
        scheduled_at_date: "",
        schedule_at_time: "",
        revert_at_date: "",
        revert_at_time: "",
        reschedule_frequency: ""
    },
    errorData: {}
};

export const actionTypes = {
    HANDLE_TASK_CHANGE: "HANDLE_TASK_CHANGE",
    HANDLE_TASK_NAME_CHANGE: "HANDLE_TASK_NAME_CHANGE",
    HANDLE_SCHEDULE_TIME_CHANGE: "HANDLE_SCHEDULE_TIME_CHANGE",
    HANDLE_ADJUSTMENT_CHANGE: "HANDLE_ADJUSTMENT_CHANGE",
    HANDLE_CONDITION_CHANGE: "HANDLE_CONDITION_CHANGE",
    REMOVE_CONDITION: "REMOVE_CONDITION",
    HANDLE_SCHEDULE_ONE_TIME_ONLY: "HANDLE_SCHEDULE_ONE_TIME_ONLY",
    HANDLE_REVERT_SCHEDULE: "HANDLE_REVERT_SCHEDULE",
    HANDLE_REVERT_TIME_CHANGE: "HANDLE_REVERT_TIME_CHANGE",
    SET_ERRORS_DATA: "SET_ERRORS_DATA", 
    SET_SELECTED_OPTIONS: "SET_SELECTED_OPTIONS"
 };

const CustomTaskReducer = (state, action) => {
    switch (action.type) {
        case actionTypes.SET_ERRORS_DATA: {
            return { ...state, errorData: action.payload };
        }
        case actionTypes.SET_SELECTED_OPTIONS:
        return {
            ...state,
            conditionsOptions: action.payload,
        };
        case actionTypes.HANDLE_TASK_CHANGE:
            return { ...state, taskType: action.payload };
        case actionTypes.HANDLE_TASK_NAME_CHANGE:
            return { ...state, taskName: action.payload };    
        case actionTypes.HANDLE_SCHEDULE_ONE_TIME_ONLY:
                return { ...state, scheduleOnlyOneTime: !state.scheduleOnlyOneTime };
        case actionTypes.HANDLE_REVERT_SCHEDULE:
                    return { ...state, revertSchedule: !state.revertSchedule };
        case actionTypes.HANDLE_SCHEDULE_TIME_CHANGE:
            return {
                ...state, scheduleData: { ...state.scheduleData, ...action.payload }
            }
        case actionTypes.HANDLE_REVERT_TIME_CHANGE:
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
            
                const updatedConditionsOptions = state.conditionsOptions.filter(
                    (option) => option !== action.payload.field
                );
            
                return {
                    ...state,
                    conditions: updatedConditions,
                    conditionsOptions: updatedConditionsOptions,
                };
            }
        default:
            return state;
    }
}

export default CustomTaskReducer;
