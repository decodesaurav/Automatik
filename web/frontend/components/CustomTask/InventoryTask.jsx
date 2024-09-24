import { InlineStack, Select, TextField } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useCallback, useReducer } from "react";
import CustomTaskReducer, { actionTypes, customeTaskState } from "../../reducers/CustomTaskReducer";

export default function InventoryTask() {
  const { t } = useTranslation();
  const [state, dispatch] = useReducer(CustomTaskReducer, customeTaskState);

  const handleAdjustmentChange = useCallback((value) => {
      dispatch({
          type: actionTypes.HANDLE_ADJUSTMENT_CHANGE,
          payload: { method: value }, // Update the method in adjustment
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
        <Select
          options={[
            { label: "Increase stock by", value: "increase" },
            { label: "Decrease stock by", value: "decrease" },
          ]}
          onChange={handleAdjustmentChange}
          value={state.adjustment.method || "increase"} // Use state from reducer
        />
        <TextField
          type="number"
          min={0}
          value={state.adjustment.value || 0} // Use state from reducer
          onChange={handleValueChange}
        />
      </InlineStack>
    </>
  );
}
