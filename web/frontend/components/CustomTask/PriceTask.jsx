import { BlockStack, InlineError, InlineStack, RadioButton, Select, TextField } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useCallback, useReducer } from "react";

export default function PriceTask({stateData,dispatch,actionTypes,errorData}) {
    let state = stateData;
  const { t } = useTranslation();
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

  return (
    <>
        
        <InlineStack gap={200}>
            <BlockStack>
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
                    value={state.adjustment?.method}
                />
                {errorData?.adjustment_method && (
                    <InlineError message={errorData.adjustment_method} fieldID="method" />
                )}
            </BlockStack>

            <BlockStack>
                <TextField
                    type="number"
                    min={0}
                    value={state.adjustment?.value}
                    onChange={handleValueChange}
                />
                {errorData?.adjustment_value && (
                    <InlineError message={errorData.adjustment_value} fieldID="value" />
                )}
            </BlockStack>

            <BlockStack>
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
                {errorData?.adjustment_type && (
                    <InlineError message={errorData.adjustment_type} fieldID="adjustmentType" />
                )}
            </BlockStack>
        </InlineStack>
    </>
  );

}
