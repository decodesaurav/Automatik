import { InlineStack, RadioButton, Select, TextField,Text, BlockStack } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useState } from "react";

export default function TitleField({state,dispatch}) {
  const { t } = useTranslation();
  const handleValueChange = (newValue) => {
    dispatch({
      type: 'HANDLE_CONDITION_CHANGE',
      payload: { field: 'title', data: { method: 'like' } },
    });
    dispatch({
      type: 'HANDLE_CONDITION_CHANGE',
      payload: { field: 'title', data: { value: newValue } },
    });
  };
  const condition = state.conditions.find(cond => cond.field === 'title') || {};
  console.log(condition)
  return (
    <>
        <BlockStack gap={100}>
            <Text>Where product title contains</Text>
            <InlineStack gap={200}>
                    <TextField
                        type="text"
                        placeholder="new T-shirt"
                        value={condition?.value}
                        onChange={handleValueChange}
                    />
                </InlineStack>
        </BlockStack>
    </>
  );

}
