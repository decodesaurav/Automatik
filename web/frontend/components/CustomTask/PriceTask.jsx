import { InlineStack, RadioButton, Select, TextField } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useReducer, useState } from "react";
import CustomTaskReducer, { actionTypes, customeTaskState } from "../../reducers/CustomTaskReducer";

export default function PriceTask() {
  const { t } = useTranslation();
  const [state, dispatch] = useReducer(CustomTaskReducer, customeTaskState);
  const [method, setMethod] = useState('increase');
  const [value, setValue] = useState(0);
  const [adjustmentType, setAdjustmentType] = useState('amount');

    // const handleAdjustmentChange = useCallback((value) => setMethod(value), []);
    // const handleAdjustmentTypeChange = useCallback(
    //     (value) => setAdjustmentType(value),
    //     []
    // );
    // const handleValueChange = useCallback((newValue) => setValue(newValue), []);


    const handleAdjustmentChange = useCallback((value) => {
        dispatch({
            type: actionTypes.HANDLE_ADJUSTMENT_CHANGE,
            payload: { method: value }, // Update the method in adjustment
        });
    }, []);

    const handleAdjustmentTypeChange = useCallback((value) => {
        dispatch({
            type: actionTypes.HANDLE_ADJUSTMENT_CHANGE,
            payload: { adjustmentType: value }, // Update the adjustment type in adjustment
        });
    }, []);

    const handleValueChange = useCallback((newValue) => {
        dispatch({
            type: actionTypes.HANDLE_ADJUSTMENT_CHANGE,
            payload: { value: newValue }, // Update the value in adjustment
        });
    }, []);

    console.log(state.adjustment)

  return (
    <>
        
        <InlineStack gap={200}>
                <Select
                    options={[
                        {
                            label: "Increase price by",
                            value: "increase",
                        },
                        {
                            label: "Decrease price by",
                            value: "decrease",
                        },
                    ]}
                    onChange={handleAdjustmentChange}
                    value={method}
                />
                <TextField
                    type="number"
                    min={0}
                    value={state.adjustment?.value}
                    onChange={handleValueChange}
                />
                <Select
                    options={[
                        {
                            label: "Fixed Amount",
                            value: "amount",
                        },
                        {
                            label: "Percent",
                            value: "percent",
                        },
                    ]}
                    onChange={handleAdjustmentTypeChange}
                    value={state.adjustment?.adjustmentType}
                />
            </InlineStack>
    </>
  );

}
